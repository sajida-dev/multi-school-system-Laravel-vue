<?php

namespace Modules\ClassesSections\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Teachers\Models\Teacher;

class Subject extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    protected $dates = ['deleted_at'];

    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'class_subject', 'subject_id', 'class_id');
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'class_subject_teacher', 'subject_id', 'teacher_id')
            ->withPivot('class_id');
    }
}
