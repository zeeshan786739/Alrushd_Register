<?php

namespace App\Services\EmailMarketing;

use App\Models\EmailMarketing\MailboxSetting;
use App\Models\EmailMarketing\SenderMailbox;
use App\Services\EmailMarketing\Contracts\MailboxClientInterface;
use Illuminate\Support\Facades\Log;

/**
 * Optional Webklex-backed client. Returns empty when package is not installed.
 * Install with: composer require webklex/php-imap:^6.2
 */
class WebklexMailboxClient implements MailboxClientInterface
{
    public function fetchNewMessages(MailboxSetting|SenderMailbox $settings, ?string $sinceUid = null): array
    {
        if (! class_exists(\Webklex\PHPIMAP\ClientManager::class)) {
            Log::warning('EmailMarketing: webklex/php-imap is not installed; inbox sync skipped.');

            return [];
        }

        if (! $settings->isImapConfigured()) {
            return [];
        }

        $cm = new \Webklex\PHPIMAP\ClientManager;
        $client = $cm->make([
            'host' => $settings->imap_host,
            'port' => $settings->imap_port ?: 993,
            'encryption' => $settings->imap_encryption ?: 'ssl',
            'validate_cert' => (bool) $settings->validate_cert,
            'username' => $settings->imap_username,
            'password' => $settings->imap_password,
            'protocol' => 'imap',
        ]);

        $client->connect();
        $folder = $client->getFolder($settings->inbox_folder ?: 'INBOX');
        $query = $folder->messages()->unseen()->limit(50);

        // Webklex 6.2 exposes incremental UID retrieval on Query itself.
        // whereUidGreaterThan() is not a supported WhereQuery condition.
        $fetched = $sinceUid
            ? $query->getByUidGreater((int) $sinceUid)
            : $query->get();

        $messages = [];
        foreach ($fetched as $message) {
            $from = $message->getFrom()->first();
            $attachments = [];
            foreach ($message->getAttachments() as $attachment) {
                $attachments[] = [
                    'name' => (string) $attachment->getName(),
                    'mime' => (string) $attachment->getMimeType(),
                    'contents' => (string) $attachment->getContent(),
                ];
            }

            $messages[] = [
                'message_id' => (string) ($message->getMessageId() ?: ''),
                'imap_uid' => (string) $message->getUid(),
                'from_email' => $from?->mail,
                'from_name' => $from?->personal,
                'to' => (string) $message->getTo(),
                'cc' => (string) $message->getCc(),
                'subject' => (string) $message->getSubject(),
                'body_html' => (string) $message->getHTMLBody(),
                'body_text' => (string) $message->getTextBody(),
                'received_at' => optional($message->getDate()?->toDate())->toDateTimeString(),
                'attachments' => $attachments,
            ];

            try {
                $message->setFlag('Seen');
            } catch (\Throwable) {
                // Non-fatal.
            }
        }

        $client->disconnect();

        return $messages;
    }
}
