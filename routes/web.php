<?php

use App\Http\Controllers\ResultsDownloadController;
use App\Livewire\CaseDemo;
use Illuminate\Support\Facades\Route;

// The only two routes. Livewire adds its own update route, which has the
// same rate limit (see AppServiceProvider).
Route::middleware('throttle:demo')->group(function () {
    Route::get('/', CaseDemo::class)->name('demo');
    Route::get('/results/download', ResultsDownloadController::class)->name('results.download');
});
