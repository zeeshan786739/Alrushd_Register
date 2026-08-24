<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class StudentYear extends Model
{
    use BelongsToOrganization;

    protected $guarded = [];

    public function group()
    {
        return $this->belongsTo(StudentGroup::class, 'group_id');
    }
}
