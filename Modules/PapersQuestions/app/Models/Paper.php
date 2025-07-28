<?php

namespace Modules\PapersQuestions\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paper extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'class_id',
        'section_id',
        'teacher_id',
        'title',
        'published',
        'total_marks',
        'time_duration',
        'course_name',
        'course_code',
        'program',
        'semester',
        'session',
        'exam_date',
        'instructions',
    ];

    protected $casts = [
        'published' => 'boolean',
        'total_marks' => 'integer',
        'time_duration' => 'integer', // in minutes
        'exam_date' => 'date',
    ];

    public function class(): BelongsTo
    {
        return $this->belongsTo(\Modules\ClassesSections\App\Models\ClassModel::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(\Modules\ClassesSections\App\Models\Section::class, 'section_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(\Modules\Teachers\Models\Teacher::class, 'teacher_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    // Get questions grouped by section
    public function getQuestionsBySection()
    {
        return $this->questions()->orderBy('section')->orderBy('question_number')->get()->groupBy('section');
    }

    // Calculate total marks
    public function getTotalMarksAttribute()
    {
        return $this->questions()->sum('marks');
    }
}
