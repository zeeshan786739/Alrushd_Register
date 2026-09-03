<?php

namespace App\Models\EmailMarketing;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SenderMailbox extends Model
{
    use BelongsToOrganization;

    protected $table = 'em_sender_mailboxes';

    protected $fillable = [
        'organization_id', 'name', 'email', 'reply_to', 'is_verified', 'is_default', 'is_active',
        'imap_host', 'imap_port', 'imap_encryption', 'imap_username', 'imap_password',
        'inbox_folder', 'validate_cert', 'last_synced_at', 'last_sync_status', 'last_sync_error',
    ];

    protected $hidden = ['imap_password'];

    protected function casts(): array
    {
        return [
            'imap_password' => 'encrypted',
            'is_verified' => 'boolean',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'validate_cert' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('is_verified', true);
    }

    public function isImapConfigured(): bool
    {
        return filled($this->imap_host) && filled($this->imap_username) && filled($this->imap_password);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_mailbox_id');
    }
}
