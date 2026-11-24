<?php

namespace App\Support;

use Illuminate\Support\Facades\Lang;

/**
 * Turns the results (Modules::results()) into plain text for the "Copy
 * results" button, so the participant can paste it into the chat of the call.
 *
 * Shared file. Uses the current language. Contains the session code, never
 * anything else that could identify a person.
 */
final class ResultsText
{
    public static function make(array $results): string
    {
        $lines = [__('demo.results.text.heading', ['code' => $results['session_code'] ?? '-'])];

        foreach ($results['modules'] as $summary) {
            $lines[] = '';
            array_push($lines, ...self::moduleLines($summary));
        }

        return implode("\n", $lines);
    }

    private static function moduleLines(array $summary): array
    {
        $module = $summary['module'];
        $class = Modules::classFor($module);
        $waits = $class::waits();

        $lines = [self::moduleTitle($module)];

        foreach ($summary['tasks'] as $task) {
            $label = $class::taskLabel($task['task']);

            if (! $task['finished']) {
                $lines[] = self::term($module, 'text.unfinished', ['task' => $label]);

                continue;
            }

            $time = self::duration($task['time_ms']);
            if ($waits) {
                $time .= ' ('.self::term($module, 'text.without_wait', ['time' => self::duration($task['time_without_wait_ms'])]).')';
            }

            $parts = array_filter([
                "{$label}: {$time}",
                $class::taskDetail($task),
                $task['outcome'] !== null ? __("{$module}.outcomes.{$task['outcome']}") : null,
                self::term($module, 'text.back', ['back' => $task['back_clicks']]),
            ]);

            $lines[] = implode(', ', $parts);
        }

        $total = self::duration($summary['total_time_ms']);
        if ($waits) {
            $total .= ' ('.self::term($module, 'text.without_wait', ['time' => self::duration($summary['total_time_without_wait_ms'])]).')';
        }
        $lines[] = self::term($module, 'text.total', ['time' => $total]).', '
            .self::term($module, 'text.back', ['back' => $summary['total_back_clicks']]);

        if ($summary['outcomes'] !== []) {
            $lines[] = self::term($module, 'text.outcomes', ['list' => self::outcomeList($module, $summary['outcomes'])]);
        }

        if ($summary['target_ms'] !== null) {
            $lines[] = self::target($module, $summary);
        }

        return $lines;
    }

    /** "Module 1: Caseworker check", or just the title for a module without a number. */
    public static function moduleTitle(string $module): string
    {
        $number = config("safestart.modules.{$module}.number");
        $title = __("demo.modules.{$module}.title");

        return $number === null ? $title : __('demo.results.module', ['n' => $number, 'title' => $title]);
    }

    /** "2 exact, 1 extra route, 0 route missed" */
    public static function outcomeList(string $module, array $counts): string
    {
        $parts = [];
        foreach ($counts as $outcome => $count) {
            $parts[] = $count.' '.__("{$module}.outcomes.{$outcome}");
        }

        return implode(', ', $parts);
    }

    /** "All cases in under 10 minutes: yes" */
    public static function target(string $module, array $summary): string
    {
        return self::term($module, 'target', [
            'minutes' => intdiv($summary['target_ms'], 60_000),
            'answer' => __($summary['under_time_target'] ? 'demo.results.yes' : 'demo.results.no'),
        ]);
    }

    /**
     * A results text: the module's own version ("<module>.results.<key>")
     * if it has one, otherwise the shared one ("demo.results.<key>").
     */
    public static function term(string $module, string $key, array $replace = []): string
    {
        return Lang::has("{$module}.results.{$key}")
            ? __("{$module}.results.{$key}", $replace)
            : __("demo.results.{$key}", $replace);
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
