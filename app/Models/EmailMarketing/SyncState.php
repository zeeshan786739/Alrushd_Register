<?php

namespace App\Models\EmailMarketing;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class SyncState extends Model
{
    use BelongsToOrganization;

    protected $table = 'em_sync_states';

    protected $fillable = [
        'organization_id', 'mailbox', 'last_uid', 'last_synced_at',
    ];

    protected function casts(): array
    {
        return ['last_synced_at' => 'datetime'];
    }
}
