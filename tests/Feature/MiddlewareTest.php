<?php

use Illuminate\Http\Request;
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

    it('never allows the Vite dev server outside local development', function () {
        $hot = public_path('hot');
        $existed = file_exists($hot);
        $previous = $existed ? file_get_contents($hot) : null;
        file_put_contents($hot, 'http://[::1]:5173');

        try {
            $csp = $this->get('/_probe')->headers->get('Content-Security-Policy');
        } finally {
            $existed ? file_put_contents($hot, $previous) : unlink($hot);
        }

        // Tests run as APP_ENV=testing, which is not "local".
        expect($csp)->not->toContain('5173');
    });

    it('allows the Vite dev server in local development', function () {
        $hot = public_path('hot');
        $existed = file_exists($hot);
        $previous = $existed ? file_get_contents($hot) : null;
        file_put_contents($hot, 'http://127.0.0.1:5173');
        app()->detectEnvironment(fn () => 'local');

        try {
            $csp = $this->get('/_probe')->headers->get('Content-Security-Policy');
        } finally {
            app()->detectEnvironment(fn () => 'testing');
            $existed ? file_put_contents($hot, $previous) : unlink($hot);
        }

        expect($csp)
            ->toContain("script-src 'self' 'nonce-")
            ->toContain('http://127.0.0.1:5173')
            ->toContain('connect-src \'self\' http://127.0.0.1:5173 ws://127.0.0.1:5173');
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

describe('trusted proxies', function () {
    beforeEach(function () {
        Route::middleware('web')->get('/_proxy', fn (Request $request) => $request->ip().'|'.($request->secure() ? 'https' : 'http'));
    });

    it('sees tunnel requests as HTTPS but ignores a forged client IP', function () {
        $response = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders(['X-Forwarded-Proto' => 'https', 'X-Forwarded-For' => '203.0.113.9'])
            ->get('/_proxy');

        expect($response->getContent())->toBe('127.0.0.1|https');
    });

    it('ignores proxy headers from anyone but loopback', function () {
        $response = $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.7'])
            ->withHeaders(['X-Forwarded-Proto' => 'https'])
            ->get('/_proxy');

        expect($response->getContent())->toBe('198.51.100.7|http');
    });
});

describe('routes', function () {
    it('has no health check or other default pages', function (string $path) {
        $this->get($path)->assertNotFound();
    })->with(['/up', '/welcome', '/login']);

    it('registers only the two demo routes besides Livewire\'s own', function () {
        $ours = collect(Route::getRoutes()->getRoutes())
            ->map->uri()
            ->reject(fn ($uri) => str_starts_with($uri, 'livewire-') || str_starts_with($uri, '_'))
            ->values()
            ->all();

        expect($ours)->toEqualCanonicalizing(['/', 'results/download']);
    });

    it('rate limits both demo routes', function (string $name) {
        expect(Route::getRoutes()->getByName($name)->gatherMiddleware())->toContain('throttle:120,1');
    })->with(['demo', 'results.download']);

    it('does not serve files from storage', function () {
        $uris = collect(Route::getRoutes()->getRoutes())->map->uri();

        expect($uris->filter(fn ($uri) => str_starts_with($uri, 'storage')))->toBeEmpty();
    });

    it('rate limits Livewire updates', function () {
        $route = collect(Route::getRoutes()->getRoutes())
            ->first(fn ($route) => str_ends_with((string) $route->getName(), 'livewire.update'));

        expect($route->gatherMiddleware())->toContain('throttle:120,1');
    });
});
