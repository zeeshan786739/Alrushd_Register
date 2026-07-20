<?php

namespace App\Models\EmailMarketing;

use App\Models\Admin;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends Model
{
    use BelongsToOrganization;
    use SoftDeletes;

    protected $table = 'em_templates';

    protected $fillable = [
        'organization_id', 'name', 'subject', 'body_html', 'body_text',
        'category', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
