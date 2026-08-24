<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class StudentGroup extends Model
{
    use BelongsToOrganization;

    protected $guarded = [];
}
