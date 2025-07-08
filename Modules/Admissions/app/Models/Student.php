<?php

namespace Modules\Admissions\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Fees\App\Models\Fee;

class Student extends Model
{
    use SoftDeletes;

    protected $table = 'students';

    protected $fillable = [
        'nationality',
        'registration_number',
        'name',
        'b_form_number',
        'admission_date',
        'date_of_birth',
        'class',
        'gender',
        'class_shift',
        'previous_school',
        'inclusive',
        'other_inclusive_type',
        'religion',
        'is_bricklin',
        'is_orphan',
        'is_qsc',
        'profile_photo_path',
        'father_name',
        'guardian_name',
        'father_cnic',
        'mother_cnic',
        'father_profession',
        'no_of_children',
        'job_type',
        'father_education',
        'mother_education',
        'mother_profession',
        'father_income',
        'mother_income',
        'household_income',
        'permanent_address',
        'phone_no',
        'mobile_no',
    ];

    public function fees()
    {
        return $this->hasMany(Fee::class);
    }
}
