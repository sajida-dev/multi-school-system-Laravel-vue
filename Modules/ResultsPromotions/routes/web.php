<?php

use Illuminate\Support\Facades\Route;
use Modules\ResultsPromotions\app\Http\Controllers\ExamTypeController;
use Modules\ResultsPromotions\Http\Controllers\AcademicResultController;
use Modules\ResultsPromotions\Http\Controllers\ExamController;
use Modules\ResultsPromotions\Http\Controllers\ExamPaperController;
use Modules\ResultsPromotions\Http\Controllers\ExamResultController;
use Modules\ResultsPromotions\Http\Controllers\ResultsPromotionsController;
use Modules\ResultsPromotions\Http\Controllers\TermResultController;

Route::middleware(['auth', 'set.active.school', 'verified', 'team.permission'])->group(function () {
    // Route::resource('results', ResultsPromotionsController::class)->names('results');
    Route::resources([
        'exam-types'       => ExamTypeController::class,
        'exams'            => ExamController::class,
        'exam-papers'      => ExamPaperController::class,
        'exam-results'     => ExamResultController::class,
        'term-results'     => TermResultController::class,
        'academic-results' => AcademicResultController::class,
    ]);
});
