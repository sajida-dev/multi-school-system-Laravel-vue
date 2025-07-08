<?php

use Illuminate\Support\Facades\Route;
use Modules\ClassesSections\Http\Controllers\ClassesSectionsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('classessections', ClassesSectionsController::class)->names('classessections');
});
