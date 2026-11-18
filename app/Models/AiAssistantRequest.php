<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One message to the AI assistant: who, which section, model, tokens and estimated cost. */
class AiAssistantRequest extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['organization_id', 'admin_id', 'section', 'model', 'tokens_in', 'tokens_out', 'cost_usd', 'rounds', 'status', 'error'];

    protected $casts = ['cost_usd' => 'float'];
}
