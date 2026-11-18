<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Audit trail: one field the AI assistant changed (old → new), grouped per reply for Undo. */
class AiAssistantChange extends Model
{
    protected $fillable = ['organization_id', 'admin_id', 'request_id', 'change_set', 'target', 'target_key', 'field', 'old_value', 'new_value', 'undone_at'];

    protected $casts = ['old_value' => 'json', 'new_value' => 'json', 'undone_at' => 'datetime'];
}
