<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

/*
| The command reads public/hot and public/build/manifest.json. These tests
| point Laravel at a temporary public folder, so the real files (and a
| running `npm run dev`) are never touched.
*/

const DEMO_PAGE = '<html><head></head><body><div data-screen="intro"></div></body></html>';

/**
 * Runs the command and returns [exit code, output].
 */
function preflight(string $options = ''): array
{
    $code = Artisan::call(trim('demo:preflight '.$options));

    return [$code, Artisan::output()];
}

beforeEach(function () {
    $this->public = sys_get_temp_dir().'/safestart-preflight-'.uniqid();
    mkdir($this->public.'/build', recursive: true);
    file_put_contents($this->public.'/build/manifest.json', '{}');
    app()->usePublicPath($this->public);

    // A setup that passes every check.
    config([
        'app.debug' => false,
        'app.env' => 'production',
        'app.key' => 'base64:'.base64_encode(str_repeat('k', 32)),
        'safestart.herd_url' => 'http://safestart-demo.test',
        'session.encrypt' => true,
        'session.secure' => true,
        'session.driver' => 'file',
        'session.http_only' => true,
        'session.same_site' => 'strict',
        'cache.default' => 'file',
        'queue.default' => 'sync',
        'logging.channels.single.level' => 'warning',
    ]);

    // Tests change $this->page to change what the home page returns;
    // null means the site cannot be reached.
    $this->page = DEMO_PAGE;
    Http::fake(fn () => $this->page === null
        ? throw new ConnectionException('down')
        : Http::response($this->page));
});

afterEach(function () {
    @unlink($this->public.'/hot');
    @unlink($this->public.'/build/manifest.json');
    @rmdir($this->public.'/build');
    @rmdir($this->public);
});

it('passes when everything is set for sharing', function () {
    [$code, $output] = preflight();

    expect($code)->toBe(0)
        ->and($output)->toContain('All checks passed')
        ->and($output)->not->toContain('FAIL');
});

it('fails with a clear message for each unsafe setting', function (array $config, string $message) {
    config($config);

    [$code, $output] = preflight();

    expect($code)->toBe(1)
        ->and($output)->toContain($message)
        ->and($output)->toContain('Do NOT share this demo yet');
})->with([
    'debug on' => [['app.debug' => true], 'Set APP_DEBUG=false'],
    'not production' => [['app.env' => 'local'], 'Set APP_ENV=production'],
    'session not encrypted' => [['session.encrypt' => false], 'Set SESSION_ENCRYPT=true'],
    'cookie not secure' => [['session.secure' => false], 'Set SESSION_SECURE_COOKIE=true'],
    'database sessions' => [['session.driver' => 'database'], 'Set SESSION_DRIVER=file'],
    'lax cookies' => [['session.same_site' => 'lax'], 'Set SESSION_SAME_SITE=strict'],
    'debug logging' => [['logging.channels.single.level' => 'debug'], 'Set LOG_LEVEL=warning'],
]);

it('fails while the Vite dev server is running', function () {
    file_put_contents($this->public.'/hot', 'http://127.0.0.1:5173');

    [$code, $output] = preflight();

    expect($code)->toBe(1)->and($output)->toContain('Stop `npm run dev`');
});

it('fails when the assets are not built', function () {
    unlink($this->public.'/build/manifest.json');

    [$code, $output] = preflight();

    expect($code)->toBe(1)->and($output)->toContain('Run `npm run build`');
});

it('fails when Herd Studio injects its script', function () {
    $this->page = str_replace('</head>', '<script type="module" src="/herd-studio/loader.js"></script></head>', DEMO_PAGE);

    [$code, $output] = preflight();

    expect($code)->toBe(1)->and($output)->toContain('Turn Herd Studio off');
});

it('warns but does not fail when the site cannot be reached', function () {
    $this->page = null;

    [$code, $output] = preflight();

    expect($code)->toBe(0)->and($output)->toContain('Could not reach');
});

it('warns when HERD_URL points at something else than this demo', function () {
    $this->page = '<html>another site</html>';

    [$code, $output] = preflight();

    expect($code)->toBe(0)->and($output)->toContain('does not show this demo');
});

it('can skip the page check', function () {
    $this->page = '<script src="/herd-studio/loader.js"></script>';

    [$code] = preflight('--skip-http');

    expect($code)->toBe(0);
});
