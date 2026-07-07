<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiContactForm extends Model
{
   protected $guarded = [];

   protected $casts = [
        'date_1' => 'array',
        'date_2' => 'array',
        'address_6' => 'array',
        'address_7' => 'array',
        'address_8' => 'array',
    ];
}
