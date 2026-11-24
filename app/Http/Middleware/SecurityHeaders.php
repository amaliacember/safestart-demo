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

        $policy = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}'",
            "style-src 'self' 'nonce-{$nonce}'",
            "img-src 'self' data:",
            "font-src 'self'",
            "connect-src 'self'",
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

        return $response;
    }
}
