<?php

use Illuminate\Support\Facades\Route;
use Modules\Teachers\Http\Controllers\TeachersController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('teachers', TeachersController::class)->names('teachers');
});
