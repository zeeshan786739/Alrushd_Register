<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormStudent extends Model
{

    protected $table = 'form_students';


    
    protected $guarded = [];

    public function submission()
    {
        return $this->belongsTo(FormSubmission::class, 'form_submission_id');
    }

//    public function studentCourses()
//     {
//        return $this->belongsTo(StudentCourse::class,'student_courses.year_id','=','form_students.year_id')->whereColumn('student_courses.package_id','=','form_students.package_id');
//     }



    public function course()
    {
        return $this->hasOne(StudentCourse::class, 'year_id', 'year_id')
                    ->whereColumn('student_courses.package_id', '=', 'form_students.package_id');
    }



    
    public function year()
    {
        return $this->belongsTo(StudentYear::class, 'year_id');
    }

    public function package()
    {
        return $this->belongsTo(StudentPackage::class, 'package_id');
    }


    public function group()
    {
        return $this->belongsTo(StudentGroup::class, 'group_id');
    }

 

    protected $casts = [
        'core_subjects' => 'array',
        'islamic_subjects' => 'array',
        'additional_subjects' => 'array',
        'language_subjects' => 'array',
        'hifdh_subjects' => 'array',
    ];
}
