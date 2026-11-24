<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // Rate limit for both demo routes and Livewire's update route.
        //
        // 120 per minute per session: far more than a person clicking through
        // a case needs, so one participant never sees a "too many requests".
        // 600 per minute per IP on top: everyone behind the herd share tunnel
        // has the same IP, so this caps the total, also for a client that
        // drops its cookie to get a fresh session on every request.
        RateLimiter::for('demo', function (Request $request) {
            return [
                Limit::perMinute(120)->by('session:'.$request->session()->getId()),
                Limit::perMinute(600)->by('ip:'.$request->ip()),
            ];
        });

        // Every click in the demo is a POST to Livewire's update route.
        Livewire::setUpdateRoute(function ($handle, $path) {
            return Route::post($path, $handle)->middleware(['web', 'throttle:demo']);
        });
    }
}
