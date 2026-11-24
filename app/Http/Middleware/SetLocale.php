<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the language the participant picked on the intro screen.
 *
 * The choice is kept in the session (never in the URL), so every request,
 * including Livewire's background updates, renders in the same language.
 * Anything not in the allowed list is ignored and the default (Dutch) is used.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');

        if (in_array($locale, config('safestart.locales'), true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
