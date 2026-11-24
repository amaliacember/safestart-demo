<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Every click in the demo is a POST to Livewire's update route, so it
        // gets the same rate limit as our own routes. 120 per minute is far more
        // than a person clicking through a case needs.
        Livewire::setUpdateRoute(function ($handle, $path) {
            return Route::post($path, $handle)->middleware(['web', 'throttle:120,1']);
        });
    }
}
