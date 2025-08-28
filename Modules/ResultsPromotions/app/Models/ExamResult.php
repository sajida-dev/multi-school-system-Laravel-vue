<?php

namespace Modules\ResultsPromotions\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Admissions\App\Models\Student;
use Modules\ResultsPromotions\Models\ExamPaper;

class ExamResult extends Model
{
    protected $guarded = [];
    use SoftDeletes;

    protected $fillable = ['exam_paper_id', 'student_id', 'marks_obtained', 'percentage', 'status', 'promotion_status', 'remarks'];

    public function examPaper()
    {
        return $this->belongsTo(ExamPaper::class);
    }
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
