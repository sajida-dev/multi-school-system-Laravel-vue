<?php

namespace Modules\ResultsPromotions\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\ResultsPromotions\Models\ExamPaper;

class Exam extends Model
{
    protected $guarded = [];
    use SoftDeletes;

    protected $fillable = ['school_id', 'title', 'exam_type_id', 'class_id', 'section_id', 'academic_year', 'start_date', 'end_date', 'status', 'instructions', 'created_by', 'updated_by'];

    public function examType()
    {
        return $this->belongsTo(ExamType::class);
    }
    public function papers()
    {
        return $this->hasMany(ExamPaper::class);
    }
    public function termResults()
    {
        return $this->hasMany(TermResult::class);
    }
}
