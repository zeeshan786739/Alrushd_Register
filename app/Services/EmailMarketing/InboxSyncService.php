<?php

namespace App\Services\EmailMarketing;

use App\Models\EmailMarketing\Message;
use App\Models\EmailMarketing\MessageAttachment;
use App\Models\EmailMarketing\SyncState;
use App\Services\EmailMarketing\Contracts\MailboxClientInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InboxSyncService
{
    public function __construct(
        private MailboxClientInterface $client,
        private MailConfigResolver $mailConfig,
        private HtmlSanitizer $sanitizer,
    ) {
    }

    public function syncOrganization(int $organizationId): array
    {
        $settings = $this->mailConfig->forOrganization($organizationId);

        if (! $settings || ! $settings->is_enabled || ! $settings->isImapConfigured()) {
            return ['imported' => 0, 'skipped' => true, 'reason' => 'imap_not_configured'];
        }

        $state = SyncState::firstOrCreate(
            ['organization_id' => $organizationId, 'mailbox' => $settings->inbox_folder ?: 'INBOX'],
            ['last_uid' => null]
        );

        try {
            $fetched = $this->client->fetchNewMessages($settings, $state->last_uid);
            $imported = 0;
            $maxUid = $state->last_uid;

            DB::transaction(function () use ($fetched, $organizationId, &$imported, &$maxUid) {
                foreach ($fetched as $item) {
                    $messageId = $item['message_id'] ?: null;
                    $imapUid = (string) ($item['imap_uid'] ?? '');

                    if ($imapUid === '') {
                        continue;
                    }

                    $exists = Message::query()
                        ->where('organization_id', $organizationId)
                        ->where(function ($q) use ($messageId, $imapUid) {
                            $q->where('imap_uid', $imapUid);
                            if ($messageId) {
                                $q->orWhere('message_id', $messageId);
                            }
                        })
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $message = Message::create([
                        'organization_id' => $organizationId,
                        'folder' => 'inbox',
                        'direction' => 'inbound',
                        'message_id' => $messageId ?: 'imap-'.$imapUid.'-'.Str::random(8),
                        'imap_uid' => $imapUid,
                        'from_email' => $item['from_email'] ?? null,
                        'from_name' => $item['from_name'] ?? null,
                        'to' => $item['to'] ?? null,
                        'cc' => $item['cc'] ?? null,
                        'subject' => $item['subject'] ?? '(no subject)',
                        'body_html' => $this->sanitizer->sanitize($item['body_html'] ?? null),
                        'body_text' => $item['body_text'] ?? $this->sanitizer->toPlainText($item['body_html'] ?? null),
                        'is_read' => false,
                        'received_at' => $item['received_at'] ?? now(),
                    ]);

                    foreach ($item['attachments'] ?? [] as $attachment) {
                        $this->storeInboundAttachment($message, $attachment);
                    }

                    $imported++;
                    if ($maxUid === null || (int) $imapUid > (int) $maxUid) {
                        $maxUid = $imapUid;
                    }
                }
            });

            $state->update(['last_uid' => $maxUid, 'last_synced_at' => now()]);
            $settings->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'success',
                'last_sync_error' => null,
            ]);

            return ['imported' => $imported, 'skipped' => false];
        } catch (\Throwable $e) {
            Log::error('EmailMarketing inbox sync failed', [
                'organization_id' => $organizationId,
                'error' => $e->getMessage(),
            ]);

            $settings->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'failed',
                'last_sync_error' => Str::limit($e->getMessage(), 500),
            ]);

            throw $e;
        }
    }

    /** @param array{name:string,mime:?string,contents:string} $attachment */
    private function storeInboundAttachment(Message $message, array $attachment): void
    {
        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $attachment['name']) ?: 'file';
        $stored = Str::uuid().'_'.$safeName;
        $path = 'email-attachments/'.$message->organization_id.'/'.$stored;
        Storage::disk('local')->put($path, $attachment['contents']);

        MessageAttachment::create([
            'organization_id' => $message->organization_id,
            'message_id' => $message->id,
            'original_name' => Str::limit($attachment['name'], 180),
            'stored_name' => $stored,
            'disk' => 'local',
            'path' => $path,
            'mime_type' => $attachment['mime'] ?? null,
            'size' => strlen($attachment['contents']),
        ]);
    }
}
