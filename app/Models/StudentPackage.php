<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class StudentPackage extends Model
{
    use BelongsToOrganization;

    protected $guarded = [];
}
