<?php

use Illuminate\Support\Facades\Route;
use Modules\ResultsPromotions\Http\Controllers\ResultsPromotionsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('resultspromotions', ResultsPromotionsController::class)->names('resultspromotions');
});
