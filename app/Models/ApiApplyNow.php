<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiApplyNow extends Model
{
    protected $guarded = [];

    protected $casts = [
        'address_1' => 'array',
        'address_2' => 'array',
        'address_3' => 'array',
        'address_4' => 'array',
        'address_5' => 'array',
        'upload_1' => 'array',
        'upload_5' => 'array',
        'upload_8' => 'array',
        'upload_11' => 'array',
        'upload_12' => 'array',
        'upload_13' => 'array',
        'upload_16' => 'array',
        'upload_17' => 'array',
        'upload_20' => 'array',
        'upload_21' => 'array',
    ];
}
