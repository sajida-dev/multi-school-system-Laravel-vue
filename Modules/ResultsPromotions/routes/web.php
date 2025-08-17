<?php

use Illuminate\Support\Facades\Route;
use Modules\ResultsPromotions\Http\Controllers\ResultsPromotionsController;

Route::middleware(['auth', 'set.active.school', 'verified'])->group(function () {
    Route::resource('results', ResultsPromotionsController::class)->names('results');
});
