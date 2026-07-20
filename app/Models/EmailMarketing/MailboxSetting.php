<?php

namespace App\Models\EmailMarketing;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailboxSetting extends Model
{
    use BelongsToOrganization;

    protected $table = 'em_mailbox_settings';

    protected $fillable = [
        'organization_id', 'from_name', 'from_email', 'reply_to',
        'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'smtp_password',
        'imap_host', 'imap_port', 'imap_encryption', 'imap_username', 'imap_password',
        'inbox_folder', 'sent_folder', 'validate_cert', 'tracking_enabled', 'is_enabled',
        'last_synced_at', 'last_sync_status', 'last_sync_error',
    ];

    protected $hidden = ['smtp_password', 'imap_password'];

    protected function casts(): array
    {
        return [
            'smtp_password' => 'encrypted',
            'imap_password' => 'encrypted',
            'validate_cert' => 'boolean',
            'tracking_enabled' => 'boolean',
            'is_enabled' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public function isSmtpConfigured(): bool
    {
        return filled($this->smtp_host) && filled($this->from_email) && filled($this->smtp_username);
    }

    public function isImapConfigured(): bool
    {
        return filled($this->imap_host) && filled($this->imap_username) && filled($this->imap_password);
    }
}
