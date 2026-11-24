<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Vite;
use Throwable;

/**
 * php artisan demo:preflight
 *
 * Run this before every session, before `herd share`. It checks that the app
 * is in its safe "sharing" state and fails with a clear message if not.
 * Run `php artisan config:clear` first if you cached the config.
 */
class DemoPreflight extends Command
{
    protected $signature = 'demo:preflight
                            {--skip-http : Do not request the home page (for example when Herd is not running)}';

    protected $description = 'Check that the demo is safe to share before a session';

    public function handle(): int
    {
        $checks = [
            // The checks asked for in the brief.
            ['APP_DEBUG is false', ! config('app.debug'), 'Set APP_DEBUG=false in .env. Debug pages can show code and settings.'],
            ['APP_ENV is production', config('app.env') === 'production', 'Set APP_ENV=production in .env.'],
            ['Vite dev server is not running (no public/hot)', ! file_exists(Vite::hotFile()), 'Stop `npm run dev` (Ctrl+C). If public/hot is still there, delete it.'],
            ['Built assets exist (public/build/manifest.json)', file_exists(public_path('build/manifest.json')), 'Run `npm run build`.'],
            ['SESSION_ENCRYPT is on', (bool) config('session.encrypt'), 'Set SESSION_ENCRYPT=true in .env.'],
            ['SESSION_SECURE_COOKIE is on', (bool) config('session.secure'), 'Set SESSION_SECURE_COOKIE=true in .env.'],

            // The rest of the .env part of the security checklist.
            ['SESSION_DRIVER is file', config('session.driver') === 'file', 'Set SESSION_DRIVER=file in .env.'],
            ['SESSION_HTTP_ONLY is on', (bool) config('session.http_only'), 'Set SESSION_HTTP_ONLY=true in .env.'],
            ['SESSION_SAME_SITE is strict', config('session.same_site') === 'strict', 'Set SESSION_SAME_SITE=strict in .env.'],
            ['CACHE_STORE is file', config('cache.default') === 'file', 'Set CACHE_STORE=file in .env.'],
            ['QUEUE_CONNECTION is sync', config('queue.default') === 'sync', 'Set QUEUE_CONNECTION=sync in .env.'],
            ['LOG_LEVEL is warning', config('logging.channels.single.level') === 'warning', 'Set LOG_LEVEL=warning in .env.'],
            ['APP_KEY is set', filled(config('app.key')), 'Run `php artisan key:generate`.'],
        ];

        if (! $this->option('skip-http')) {
            $checks[] = $this->herdStudioCheck();
        }

        $failed = 0;

        foreach ($checks as [$label, $ok, $fix]) {
            if ($ok === null) {
                $this->components->warn("{$label}: {$fix}");

                continue;
            }

            if ($ok) {
                $this->components->twoColumnDetail($label, '<fg=green;options=bold>OK</>');
            } else {
                $failed++;
                $this->components->twoColumnDetail($label, '<fg=red;options=bold>FAIL</>');
                $this->line("    <fg=red>→ {$fix}</>");
            }
        }

        $this->newLine();

        if ($failed > 0) {
            $this->components->error("{$failed} check(s) failed. Do NOT share this demo yet.");

            return self::FAILURE;
        }

        $this->components->info('All checks passed. You can run `herd share` now.');

        return self::SUCCESS;
    }

    /**
     * Herd Studio injects a script into every page through Herd's own web
     * server. That must be off before sharing. We look at the real home page,
     * as served by Herd, to be sure.
     *
     * @return array{string, bool|null, string} null means "could not check"
     */
    private function herdStudioCheck(): array
    {
        // The address Herd itself serves (what `herd share` tunnels), not
        // APP_URL: `php artisan serve` would skip Herd's web server and hide
        // the injection.
        $url = config('safestart.herd_url');
        $label = "Herd Studio is off (no /herd-studio script at {$url})";

        try {
            $html = Http::withoutVerifying()->timeout(5)->get($url)->body();
        } catch (Throwable) {
            return [$label, null, "Could not reach {$url}. Is Herd running? Set HERD_URL in .env if the site has another address, or run again with --skip-http."];
        }

        // Only trust the answer if it really is this demo's intro page.
        if (! str_contains($html, 'data-screen=')) {
            return [$label, null, "{$url} does not show this demo. Set HERD_URL in .env to this site's Herd address."];
        }

        return [
            $label,
            ! str_contains($html, '/herd-studio/'),
            'Turn Herd Studio off (README, "Turning Herd Studio off").',
        ];
    }
}
