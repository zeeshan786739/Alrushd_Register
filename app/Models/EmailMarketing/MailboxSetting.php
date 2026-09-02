<?php

namespace App\Models\EmailMarketing;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class MailboxSetting extends Model
{
    use BelongsToOrganization;

    protected $table = 'em_mailbox_settings';

    protected $fillable = [
        'organization_id', 'from_name', 'from_email', 'reply_to',
        'inbound_domain', 'inbound_enabled',
        'sendgrid_api_key', 'sendgrid_event_webhook_public_key',
        'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'smtp_password',
        'imap_host', 'imap_port', 'imap_encryption', 'imap_username', 'imap_password',
        'inbox_folder', 'sent_folder', 'validate_cert', 'tracking_enabled',
        'open_tracking', 'click_tracking', 'sendgrid_asm_group_id', 'is_enabled',
        'last_synced_at', 'last_sync_status', 'last_sync_error',
    ];

    protected $hidden = [
        'sendgrid_api_key', 'sendgrid_event_webhook_public_key',
        'smtp_password', 'imap_password',
    ];

    protected function casts(): array
    {
        return [
            'sendgrid_api_key' => 'encrypted',
            'sendgrid_event_webhook_public_key' => 'encrypted',
            'smtp_password' => 'encrypted',
            'imap_password' => 'encrypted',
            'validate_cert' => 'boolean',
            'tracking_enabled' => 'boolean',
            'open_tracking' => 'boolean',
            'click_tracking' => 'boolean',
            'inbound_enabled' => 'boolean',
            'is_enabled' => 'boolean',
            'sendgrid_asm_group_id' => 'integer',
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

    public function isSendReady(): bool
    {
        if (! $this->is_enabled || ! filled($this->from_email)) {
            return false;
        }

        return filled($this->sendgrid_api_key)
            || filled(config('sendgrid.api_key'))
            || $this->isSmtpConfigured();
    }
}
