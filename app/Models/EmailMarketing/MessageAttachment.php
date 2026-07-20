<?php

namespace App\Models\EmailMarketing;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MessageAttachment extends Model
{
    use BelongsToOrganization;

    protected $table = 'em_message_attachments';

    protected $fillable = [
        'organization_id', 'message_id', 'original_name', 'stored_name',
        'disk', 'path', 'mime_type', 'size',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'message_id');
    }

    public function absolutePath(): string
    {
        return Storage::disk($this->disk)->path($this->path);
    }
}
