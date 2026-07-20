<?php

namespace App\Models\Crm;

use App\Models\Admin;
use App\Support\OrganizationContext;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use BelongsToOrganization;
    use SoftDeletes;

    protected $table = 'crm_projects';

    protected $fillable = [
        'organization_id', 'customer_id', 'name', 'project_code', 'description', 'budget',
        'actual_cost', 'start_date', 'end_date', 'actual_end_date', 'status', 'progress',
        'priority', 'assigned_to', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'actual_cost' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'actual_end_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_to');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'project_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'project_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'project_id');
    }

    public function recalculateProgress(): void
    {
        $tasks = $this->tasks;
        if ($tasks->isEmpty()) {
            return;
        }

        $completed = $tasks->where('status', 'completed')->count();
        $this->update(['progress' => (int) round(($completed / $tasks->count()) * 100)]);
    }

    public static function generateProjectCode(?int $organizationId = null): string
    {
        $organizationId ??= OrganizationContext::idOrFail();
        $last = static::withTrashed()
            ->where('organization_id', $organizationId)
            ->orderByDesc('id')
            ->first();

        $number = $last ? ((int) substr($last->project_code, 4)) + 1 : 1;

        return 'PRJ-'.str_pad((string) $number, 5, '0', STR_PAD_LEFT);
    }
}
