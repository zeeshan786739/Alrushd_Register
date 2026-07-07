<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAdmissionForm extends Model
{
    protected $guarded = [];

    protected $casts = [
        'certificated'   => 'array',
    ];
}

