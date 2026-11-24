<?php

namespace App\Support;

use App\Livewire\Modules\Module;

/**
 * The list of modules from config/safestart/modules.php, plus which of them
 * exist yet.
 *
 * A module is "available" once its Livewire class exists. Until then the
 * start page and /m/<key> show "coming soon".
 */
final class Modules
{
    /**
     * @return array<string, array{key: string, number: int|null, class: string, listed: bool, available: bool}>
     */
    public static function all(): array
    {
        $modules = [];

        foreach (config('safestart.modules') as $key => $module) {
            $modules[$key] = [
                'key' => $key,
                'number' => $module['number'] ?? null,
                'class' => $module['class'],
                'listed' => $module['listed'] ?? true,
                'available' => class_exists($module['class']) && is_subclass_of($module['class'], Module::class),
            ];
        }

        return $modules;
    }

    /** The modules shown on the start page. */
    public static function listed(): array
    {
        return array_filter(self::all(), fn ($module) => $module['listed']);
    }

    /** @return class-string<Module>|null */
    public static function classFor(string $key): ?string
    {
        $module = self::all()[$key] ?? null;

        return $module !== null && $module['available'] ? $module['class'] : null;
    }

    /**
     * Everything for the shared results screen and the download: one block
     * per module the participant did in this session, in the order they
     * started them.
     */
    public static function results(SessionLog $log): array
    {
        $modules = [];

        foreach ($log->modules() as $key) {
            $class = self::classFor($key);

            if ($class === null) {
                continue;
            }

            $minutes = $class::targetMinutes();

            $modules[] = $log->moduleSummary(
                $key,
                $class::tasks(),
                array_keys($class::outcomes()),
                $minutes === null ? null : $minutes * 60_000,
            );
        }

        return [
            'session_code' => $log->sessionCode(),
            'locale' => $log->toArray()['locale'],
            'modules' => $modules,
        ];
    }
}
