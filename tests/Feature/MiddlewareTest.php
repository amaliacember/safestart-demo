<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Vite;

/*
| The real screens arrive in step 2. Until then these tests use a throwaway
| route inside the "web" group, which is where both middleware are attached.
*/
beforeEach(function () {
    Route::middleware('web')->get('/_probe', fn () => App::getLocale().'|'.Vite::cspNonce());
});

describe('SecurityHeaders', function () {
    it('sends a strict content security policy with a nonce', function () {
        $response = $this->get('/_probe');
        [, $nonce] = explode('|', $response->getContent());

        expect($nonce)->not->toBeEmpty();
        $response->assertHeader('Content-Security-Policy', implode('; ', [
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
        ]));
    });

    it('uses a different nonce on every request', function () {
        $first = $this->get('/_probe')->getContent();
        $second = $this->get('/_probe')->getContent();

        expect($first)->not->toBe($second);
    });

    it('sends the other security headers', function () {
        $this->get('/_probe')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    });
});

describe('SetLocale', function () {
    it('defaults to Dutch', function () {
        expect($this->get('/_probe')->getContent())->toStartWith('nl|');
    });

    it('uses the language stored in the session', function () {
        expect($this->withSession(['locale' => 'en'])->get('/_probe')->getContent())->toStartWith('en|');
    });

    it('ignores a language that is not allowed', function () {
        expect($this->withSession(['locale' => 'fr'])->get('/_probe')->getContent())->toStartWith('nl|');
    });
});

describe('routes', function () {
    it('has no welcome page and no health check', function (string $path) {
        $this->get($path)->assertNotFound();
    })->with(['/', '/up']);

    it('does not serve files from storage', function () {
        $uris = collect(Route::getRoutes()->getRoutes())->map->uri();

        expect($uris->filter(fn ($uri) => str_starts_with($uri, 'storage')))->toBeEmpty();
    });

    it('rate limits Livewire updates', function () {
        $route = collect(Route::getRoutes()->getRoutes())
            ->first(fn ($route) => str_ends_with((string) $route->getName(), 'livewire.update'));

        expect($route->gatherMiddleware())->toContain('throttle:60,1');
    });
});
