<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSubjectTeacher extends Model
{
    protected $table = 'class_subject_teacher';
    protected $fillable = ['teacher_id', 'class_id', 'subject_id'];
}
