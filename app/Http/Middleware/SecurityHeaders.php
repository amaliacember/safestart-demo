<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds strict browser security headers to every web response.
 *
 * The Content-Security-Policy only allows scripts and styles from our own
 * domain, plus inline tags that carry this request's random nonce. Vite and
 * Livewire both read the nonce from Vite::cspNonce() and add it to the tags
 * they print, so nothing else needs to pass it around.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Generates a fresh random nonce for this request.
        $nonce = Vite::useCspNonce();

        $response = $next($request);

        // Local development only: while `npm run dev` runs, assets come from
        // the Vite dev server on another port. Never in production, so a
        // shared session always uses the built files.
        $dev = '';
        $devSocket = '';
        if (app()->isLocal() && Vite::isRunningHot()) {
            $dev = ' '.trim(file_get_contents(Vite::hotFile()));
            $devSocket = ' '.preg_replace('#^http#', 'ws', trim($dev));
        }

        $policy = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}'{$dev}",
            "style-src 'self' 'nonce-{$nonce}'{$dev}",
            "img-src 'self' data:",
            "font-src 'self'{$dev}",
            "connect-src 'self'{$dev}{$devSocket}",
            "object-src 'none'",
            "base-uri 'none'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ]);

        $response->headers->set('Content-Security-Policy', $policy);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        // A demo link must never end up in a search engine.
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        // PHP adds "X-Powered-By: PHP/8.x" by itself; no need to tell visitors.
        $response->headers->remove('X-Powered-By');
        header_remove('X-Powered-By');

        return $response;
    }
}
