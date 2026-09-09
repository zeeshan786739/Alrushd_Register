<?php

namespace App\Services\EmailMarketing;

use App\Models\EmailMarketing\MailboxSetting;
use App\Models\EmailMarketing\SenderMailbox;
use Illuminate\Support\Str;

class ImapConnectionTester
{
    /**
     * @return array{ok:bool,message:string}
     */
    public function test(MailboxSetting|SenderMailbox $settings): array
    {
        if (! class_exists(\Webklex\PHPIMAP\ClientManager::class)) {
            return [
                'ok' => false,
                'message' => 'IMAP client package is not installed. Run: composer require webklex/php-imap:^6.2',
            ];
        }

        if (! $settings->isImapConfigured()) {
            return [
                'ok' => false,
                'message' => 'IMAP host, username, and password are required.',
            ];
        }

        try {
            $cm = new \Webklex\PHPIMAP\ClientManager;
            $client = $cm->make([
                'host' => trim((string) $settings->imap_host),
                'port' => (int) ($settings->imap_port ?: 993),
                'encryption' => $settings->imap_encryption ?: 'ssl',
                'validate_cert' => (bool) $settings->validate_cert,
                'username' => trim((string) $settings->imap_username),
                'password' => (string) $settings->imap_password,
                'protocol' => 'imap',
                'timeout' => 20,
            ]);

            $client->connect();
            $client->getFolder($settings->inbox_folder ?: 'INBOX');
            $client->disconnect();

            return ['ok' => true, 'message' => 'Inbox connection successful.'];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => $this->friendlyError($e->getMessage()),
            ];
        }
    }

    private function friendlyError(string $raw): string
    {
        $raw = trim($raw);

        if (Str::contains(Str::lower($raw), ['authenticationfailed', 'authentication failed', 'invalid credentials', 'login failed'])) {
            return 'Authentication failed. Type the mailbox password again (do not leave blank). Use the email account password from your email provider panel — not your website/hosting login password.';
        }

        return Str::limit($raw, 500);
    }
}
