<?php

namespace Modules\Admissions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admissions\Database\Factories\StudentEnrollmentFactory;

class StudentEnrollment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): StudentEnrollmentFactory
    // {
    //     // return StudentEnrollmentFactory::new();
    // }
}
