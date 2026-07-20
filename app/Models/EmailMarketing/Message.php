<?php

namespace App\Models\EmailMarketing;

use App\Models\Admin;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use BelongsToOrganization;
    use SoftDeletes;

    protected $table = 'em_messages';

    protected $fillable = [
        'organization_id', 'folder', 'direction', 'message_id', 'imap_uid', 'thread_id', 'parent_id',
        'from_email', 'from_name', 'to', 'cc', 'bcc', 'subject', 'body_html', 'body_text',
        'delivery_status', 'delivery_error', 'is_read', 'is_starred',
        'lead_id', 'customer_id', 'created_by', 'sent_at', 'received_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'is_starred' => 'boolean',
            'sent_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class, 'message_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function scopeInbox(Builder $query): Builder
    {
        return $query->where('folder', 'inbox');
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('folder', 'sent');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('folder', 'draft');
    }

    public function scopeStarred(Builder $query): Builder
    {
        return $query->where('is_starred', true);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function toRecipients(): array
    {
        return $this->parseAddressList($this->to);
    }

    public function parseAddressList(?string $value): array
    {
        if (! $value) {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/[,;]+/', $value) ?: [])));
    }
}
