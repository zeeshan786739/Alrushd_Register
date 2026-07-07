<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $guarded = [];

    protected $casts = [
        'education' => 'array',
        'previous_employment' => 'array',
        'references' => 'array',
        'upload_certificates' => 'array',
        'true_and_complete' => 'boolean',
        'recruitment_purposes' => 'boolean',
    ];
}

