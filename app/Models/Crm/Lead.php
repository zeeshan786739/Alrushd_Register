<?php

namespace App\Models\Crm;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Models\Admin;
use App\Models\FormEntry;
use App\Models\FormSubmission;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use BelongsToOrganization;
    use SoftDeletes;

    protected $table = 'crm_leads';

    protected $fillable = [
        'organization_id', 'form_submission_id', 'form_entry_id', 'enquire_id', 'referral_id',
        'customer_id', 'source', 'title', 'first_name', 'last_name', 'email', 'phone', 'company',
        'selected_school', 'lead_source', 'lead_status', 'priority', 'assigned_to',
        'estimated_value', 'probability', 'next_follow_up_date', 'next_follow_up_time',
        'next_follow_up_type', 'appointment_date', 'appointment_type', 'appointment_notes',
        'lead_description', 'address', 'city', 'province', 'postal_code',
        'is_converted', 'converted_at', 'last_contacted_at', 'contact_count', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'estimated_value' => 'decimal:2',
            'next_follow_up_date' => 'date',
            'appointment_date' => 'datetime',
            'is_converted' => 'boolean',
            'converted_at' => 'datetime',
            'last_contacted_at' => 'datetime',
        ];
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_to');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function formSubmission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class, 'form_submission_id');
    }

    public function formEntry(): BelongsTo
    {
        return $this->belongsTo(FormEntry::class, 'form_entry_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class, 'lead_id')->latest();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class, 'lead_id')->latest();
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.($this->last_name ?? ''));
    }

    public function logActivity(string $type, string $description, ?array $metadata = null): LeadActivity
    {
        return $this->activities()->create([
            'organization_id' => $this->organization_id,
            'admin_id' => auth('admin')->id(),
            'activity_type' => $type,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    public function scopeFollowUpToday($query)
    {
        return $query->whereDate('next_follow_up_date', today());
    }

    public function scopeOverdueFollowUp($query)
    {
        return $query->whereDate('next_follow_up_date', '<', today())
            ->whereNotIn('lead_status', [LeadStatus::Won->value, LeadStatus::Lost->value]);
    }

    public static function statusOptions(): array
    {
        return LeadStatus::options();
    }

    public static function priorityOptions(): array
    {
        return LeadPriority::options();
    }
}
