<?php

namespace App\Services\EmailMarketing;

use App\Models\EmailMarketing\MailboxSetting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SendGridWebhookVerifier
{
    public function verifyEventWebhook(Request $request): bool
    {
        $publicKeys = $this->eventWebhookPublicKeys();
        if ($publicKeys === []) {
            // Fail closed outside testing when key missing.
            return app()->environment('testing') && $request->header('X-Test-SendGrid-Event') === '1';
        }

        $signature = (string) $request->header('X-Twilio-Email-Event-Webhook-Signature', '');
        $timestamp = (string) $request->header('X-Twilio-Email-Event-Webhook-Timestamp', '');
        $payload = $request->getContent();

        if ($signature === '' || $timestamp === '' || $payload === '') {
            return false;
        }

        $decoded = base64_decode($signature, true);
        if ($decoded === false) {
            return false;
        }

        foreach ($publicKeys as $publicKey) {
            $key = openssl_pkey_get_public($this->normalizePublicKey($publicKey));
            if ($key === false) {
                continue;
            }

            if (openssl_verify($timestamp.$payload, $decoded, $key, OPENSSL_ALGO_SHA256) === 1) {
                return true;
            }
        }

        Log::warning('SendGrid event webhook signature did not match a configured account.');

        return false;
    }

    public function verifyInboundBasicAuth(Request $request): bool
    {
        $user = (string) config('sendgrid.inbound_basic_user');
        $pass = (string) config('sendgrid.inbound_basic_pass');

        if ($user === '' || $pass === '') {
            return app()->environment('testing') && $request->header('X-Test-SendGrid-Inbound') === '1';
        }

        return $request->getUser() === $user && $request->getPassword() === $pass;
    }

    private function normalizePublicKey(string $key): string
    {
        $key = trim($key);
        if (str_contains($key, 'BEGIN PUBLIC KEY')) {
            return $key;
        }

        $wrapped = trim(chunk_split($key, 64, "\n"));

        return "-----BEGIN PUBLIC KEY-----\n{$wrapped}\n-----END PUBLIC KEY-----";
    }

    /** @return list<string> */
    private function eventWebhookPublicKeys(): array
    {
        $keys = [];
        $global = trim((string) config('sendgrid.event_webhook_public_key'));
        if ($global !== '') {
            $keys[] = $global;
        }

        MailboxSetting::query()
            ->whereNotNull('sendgrid_event_webhook_public_key')
            ->select(['id', 'sendgrid_event_webhook_public_key'])
            ->each(function (MailboxSetting $settings) use (&$keys): void {
                try {
                    $key = trim((string) $settings->sendgrid_event_webhook_public_key);
                    if ($key !== '') {
                        $keys[] = $key;
                    }
                } catch (DecryptException) {
                    Log::warning('Unable to decrypt a tenant SendGrid webhook verification key.', [
                        'mailbox_setting_id' => $settings->id,
                    ]);
                }
            });

        return array_values(array_unique($keys));
    }
}
