<?php

namespace App\Support;

/**
 * Turns the results summary into plain text for the "Copy results" button,
 * so the participant can paste it into the chat of the video call.
 *
 * Uses the current language. Contains the session code, never anything else
 * that could identify a person.
 */
final class ResultsText
{
    public static function make(array $summary): string
    {
        $lines = [
            __('demo.results.text.heading', ['code' => $summary['session_code'] ?? '-']),
            '',
        ];

        foreach ($summary['cases'] as $case) {
            $name = __("demo.scenarios.{$case['scenario']}.name");

            if (! $case['finished']) {
                $lines[] = __('demo.results.text.case_unfinished', ['scenario' => $case['scenario'], 'name' => $name]);

                continue;
            }

            $lines[] = __('demo.results.text.case', [
                'scenario' => $case['scenario'],
                'name' => $name,
                'time' => self::duration($case['time_ms']),
                'without_scan' => self::duration($case['time_without_scan_ms']),
                'routes' => implode(', ', array_map(fn ($route) => __("demo.routes.{$route}.title"), $case['routes'] ?? [])),
                'outcome' => __('demo.outcomes.'.($case['route_outcome'] ?? 'missed')),
                'back' => $case['back_clicks'],
            ]);
        }

        $outcomes = $summary['route_outcomes'];

        $lines[] = '';
        $lines[] = __('demo.results.text.total', [
            'time' => self::duration($summary['total_time_ms']),
            'without_scan' => self::duration($summary['total_time_without_scan_ms']),
        ]);
        $lines[] = __('demo.results.text.outcomes', [
            'exact' => $outcomes['exact'],
            'extra' => $outcomes['extra'],
            'missed' => $outcomes['missed'],
        ]);
        $lines[] = __('demo.results.text.back', ['back' => $summary['total_back_clicks']]);
        $lines[] = __('demo.results.text.target', [
            'minutes' => config('safestart.timing.target_total_minutes'),
            'answer' => __($summary['under_time_target'] ? 'demo.results.yes' : 'demo.results.no'),
        ]);

        return implode("\n", $lines);
    }

    /**
     * Milliseconds as minutes:seconds, e.g. 125000 -> "2:05".
     */
    public static function duration(?int $ms): string
    {
        if ($ms === null) {
            return '–';
        }

        $seconds = (int) round($ms / 1000);

        return sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);
    }
}
