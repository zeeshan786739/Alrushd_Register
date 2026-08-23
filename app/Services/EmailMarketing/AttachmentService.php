<?php

namespace App\Services\EmailMarketing;

use App\Models\EmailMarketing\Message;
use App\Models\EmailMarketing\MessageAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentService
{
    private const MAX_BYTES = 10 * 1024 * 1024;

    private const ALLOWED_MIME = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'text/plain',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    private const BLOCKED_EXTENSIONS = [
        'php', 'phtml', 'phar', 'exe', 'bat', 'cmd', 'com', 'msi', 'scr', 'js', 'mjs',
        'html', 'htm', 'shtml', 'svg', 'xml', 'sh', 'bash', 'ps1', 'cgi', 'jar', 'dll',
    ];

    public function storeUpload(Message $message, UploadedFile $file): MessageAttachment
    {
        if ($file->getSize() > self::MAX_BYTES) {
            throw new \InvalidArgumentException('Attachment exceeds 10MB limit.');
        }

        $original = Str::limit((string) $file->getClientOriginalName(), 180);
        $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        if ($extension !== '' && in_array($extension, self::BLOCKED_EXTENSIONS, true)) {
            throw new \InvalidArgumentException('Attachment file type is not allowed.');
        }

        $mime = $file->getMimeType() ?: 'application/octet-stream';
        if (! in_array($mime, self::ALLOWED_MIME, true)) {
            throw new \InvalidArgumentException('Attachment MIME type is not allowed.');
        }

        // Never use the original filename as the storage path.
        $stored = (string) Str::uuid().($extension !== '' ? '.'.$extension : '');
        $directory = 'email-attachments/'.$message->organization_id;
        $path = $directory.'/'.$stored;
        Storage::disk('local')->putFileAs($directory, $file, $stored);

        return MessageAttachment::create([
            'organization_id' => $message->organization_id,
            'message_id' => $message->id,
            'original_name' => $original !== '' ? $original : $stored,
            'stored_name' => $stored,
            'disk' => 'local',
            'path' => $path,
            'mime_type' => $mime,
            'size' => $file->getSize() ?: 0,
        ]);
    }

    /**
     * Best-effort inbound store — rejects unsafe files without aborting ingest.
     */
    public function tryStoreUpload(Message $message, UploadedFile $file): ?MessageAttachment
    {
        try {
            return $this->storeUpload($message, $file);
        } catch (\Throwable $e) {
            Log::info('Inbound attachment rejected', [
                'message_id' => $message->id,
                'organization_id' => $message->organization_id,
                'reason' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function download(MessageAttachment $attachment): StreamedResponse
    {
        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        return Storage::disk($attachment->disk)->download(
            $attachment->path,
            $attachment->original_name,
            [
                'Content-Type' => $attachment->mime_type ?: 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
                'Content-Disposition' => 'attachment; filename="'.addslashes($attachment->original_name).'"',
            ]
        );
    }
}
