<?php

namespace App\Services\EmailMarketing;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SendGridWebhookVerifier
{
    public function verifyEventWebhook(Request $request): bool
    {
        $publicKey = (string) config('sendgrid.event_webhook_public_key');
        if ($publicKey === '') {
            // Fail closed outside testing when key missing.
            return app()->environment('testing') && $request->header('X-Test-SendGrid-Event') === '1';
        }

        $signature = (string) $request->header('X-Twilio-Email-Event-Webhook-Signature', '');
        $timestamp = (string) $request->header('X-Twilio-Email-Event-Webhook-Timestamp', '');
        $payload = $request->getContent();

        if ($signature === '' || $timestamp === '' || $payload === '') {
            return false;
        }

        $normalizedKey = $this->normalizePublicKey($publicKey);
        $key = openssl_pkey_get_public($normalizedKey);
        if ($key === false) {
            Log::warning('SendGrid event webhook public key is invalid.');

            return false;
        }

        $decoded = base64_decode($signature, true);
        if ($decoded === false) {
            return false;
        }

        $verified = openssl_verify($timestamp.$payload, $decoded, $key, OPENSSL_ALGO_SHA256);

        return $verified === 1;
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
}
