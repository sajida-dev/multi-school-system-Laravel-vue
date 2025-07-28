<?php

use Illuminate\Support\Facades\Route;
use Modules\PapersQuestions\Http\Controllers\PapersQuestionsController;

Route::middleware(['auth', 'set.active.school', 'verified'])->group(function () {
    Route::resource('papersquestions', PapersQuestionsController::class)->names('papersquestions');
});
