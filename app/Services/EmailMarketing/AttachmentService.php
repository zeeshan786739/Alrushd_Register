<?php

namespace App\Services\EmailMarketing;

use App\Models\EmailMarketing\Message;
use App\Models\EmailMarketing\MessageAttachment;
use Illuminate\Http\UploadedFile;
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

    public function storeUpload(Message $message, UploadedFile $file): MessageAttachment
    {
        if ($file->getSize() > self::MAX_BYTES) {
            throw new \InvalidArgumentException('Attachment exceeds 10MB limit.');
        }

        $mime = $file->getMimeType() ?: 'application/octet-stream';
        if (! in_array($mime, self::ALLOWED_MIME, true)) {
            throw new \InvalidArgumentException('Attachment MIME type is not allowed.');
        }

        $original = Str::limit($file->getClientOriginalName(), 180);
        $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', $original) ?: 'file';
        $stored = Str::uuid().'_'.$safe;
        $path = 'email-attachments/'.$message->organization_id.'/'.$stored;
        Storage::disk('local')->putFileAs('email-attachments/'.$message->organization_id, $file, $stored);

        return MessageAttachment::create([
            'organization_id' => $message->organization_id,
            'message_id' => $message->id,
            'original_name' => $original,
            'stored_name' => $stored,
            'disk' => 'local',
            'path' => $path,
            'mime_type' => $mime,
            'size' => $file->getSize() ?: 0,
        ]);
    }

    public function download(MessageAttachment $attachment): StreamedResponse
    {
        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        return Storage::disk($attachment->disk)->download(
            $attachment->path,
            $attachment->original_name
        );
    }
}
