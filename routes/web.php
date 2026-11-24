<?php

use App\Http\Controllers\ResultsDownloadController;
use App\Livewire\ComingSoon;
use App\Livewire\Results;
use App\Livewire\StartPage;
use App\Support\Modules;
use Illuminate\Support\Facades\Route;

/*
| Shared file: change it only after telling the group.
|
| You do not need to add a route for your module. Every module in
| config/safestart/modules.php gets /m/<key> here: its own Livewire class
| once that class exists, a "coming soon" page until then.
|
| Livewire adds its own update route, which has the same rate limit (see
| AppServiceProvider).
*/

Route::middleware('throttle:demo')->group(function () {
    Route::get('/', StartPage::class)->name('start');

    foreach (Modules::all() as $key => $module) {
        if ($module['available']) {
            Route::get("/m/{$key}", $module['class'])->name("module.{$key}");
        } else {
            Route::get("/m/{$key}", ComingSoon::class)->defaults('module', $key)->name("module.{$key}");
        }
    }

    Route::get('/results', Results::class)->name('results');
    Route::get('/results/download', ResultsDownloadController::class)->name('results.download');
});
