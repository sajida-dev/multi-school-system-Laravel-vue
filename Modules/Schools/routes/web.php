<?php

use Illuminate\Support\Facades\Route;
use Modules\Schools\Http\Controllers\SchoolsController;

Route::middleware(['auth', 'set.active.school', 'verified'])->group(function () {
    Route::resource('schools', SchoolsController::class)->names('schools');
});
