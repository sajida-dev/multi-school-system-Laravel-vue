<?php

namespace Modules\Teachers\Models;

use App\Models\ClassSubjectTeacher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\App\Models\Subject;
use Modules\Schools\App\Models\School;

class Teacher extends Model
{
    use SoftDeletes;

    protected $table = 'teachers';

    protected $fillable = [
        'user_id',
        'school_id',
        'cnic',
        'gender',
        'marital_status',
        'role_id',
        'dob',
        'salary',
        'contact_no',
        'date_of_joining',
        'experience_years',
        'class_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function subjectAssignments()
    {
        return $this->hasMany(ClassSubjectTeacher::class);
    }
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subject_teacher', 'teacher_id', 'subject_id')
            ->withPivot('class_id');
    }

    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_id');
    }
}
