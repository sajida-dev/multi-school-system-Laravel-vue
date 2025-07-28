<?php

use Illuminate\Support\Facades\Route;
use Modules\Fees\Http\Controllers\FeesController;

Route::middleware(['auth', 'set.active.school', 'verified'])->group(function () {
    Route::resource('fees', FeesController::class)->names('fees');
    Route::get('fees/classes/by-school', [FeesController::class, 'getClasses'])->name('fees.classes.by-school');
    Route::get('fees/students/by-class', [FeesController::class, 'getStudents'])->name('fees.students.by-class');
});
