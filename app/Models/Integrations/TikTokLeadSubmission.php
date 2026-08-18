<?php

namespace App\Models\Integrations;

use App\Enums\TikTokLeadSubmissionStatus;
use App\Models\Crm\Lead;
use App\Models\FormEntry;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TikTokLeadSubmission extends Model
{
    use BelongsToOrganization;

    protected $table = 'tiktok_lead_submissions';

    protected $fillable = [
        'organization_id',
        'integration_connection_id',
        'tiktok_form_mapping_id',
        'advertiser_id',
        'tiktok_lead_id',
        'tiktok_page_id',
        'status',
        'webhook_meta',
        'field_data',
        'form_entry_id',
        'lead_id',
        'error_message',
        'received_at',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TikTokLeadSubmissionStatus::class,
            'webhook_meta' => 'array',
            'field_data' => 'array',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(IntegrationConnection::class, 'integration_connection_id');
    }

    public function formMapping(): BelongsTo
    {
        return $this->belongsTo(TikTokFormMapping::class, 'tiktok_form_mapping_id');
    }

    public function formEntry(): BelongsTo
    {
        return $this->belongsTo(FormEntry::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function displayName(): string
    {
        $fields = is_array($this->field_data) ? $this->field_data : [];
        $name = trim((string) ($fields['name'] ?? ''));
        if ($name !== '') {
            return $name;
        }

        $first = trim((string) ($fields['first_name'] ?? ''));
        $last = trim((string) ($fields['last_name'] ?? ''));
        $combined = trim($first.' '.$last);
        if ($combined !== '') {
            return $combined;
        }

        $email = trim((string) ($fields['email'] ?? ''));

        return $email !== '' ? $email : 'TikTok lead';
    }

    public function formDisplayName(): string
    {
        $mapped = trim((string) ($this->formMapping?->external_form_name ?? ''));
        if ($mapped !== '') {
            return $mapped;
        }

        $meta = is_array($this->webhook_meta) ? $this->webhook_meta : [];
        $pageName = trim((string) ($meta['page_name'] ?? ''));
        if ($pageName !== '') {
            return $pageName;
        }

        $pageId = trim((string) ($this->tiktok_page_id ?? ''));

        return $pageId !== '' ? $pageId : 'TikTok Instant Form';
    }
}
