<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Turns the six quick-check answers into routes and package advice.
 *
 * Pure logic: no session, no Livewire, no database. The rules themselves live
 * in config/safestart.php, so a teammate can change them without touching
 * this class. See that file for the condition format.
 */
final class Triage
{
    public const YES = 'yes';

    public const NO = 'no';

    public const UNKNOWN = 'unknown';

    public const NO_ROUTE = 'none';

    /**
     * @param  array  $config  the full config('safestart') array
     */
    public function __construct(private array $config) {}

    public static function fromConfig(): self
    {
        return new self(config('safestart'));
    }

    /**
     * Which routes apply, in config order. Returns ['none'] when nothing applies.
     *
     * @param  array<string, string>  $answers  e.g. ['q1' => 'yes', 'q2' => 'unknown', ...]
     * @return list<string>
     */
    public function routes(array $answers): array
    {
        $this->assertComplete($answers);

        $routes = [];

        foreach ($this->config['rules'] as $route => $rule) {
            $context = [
                'answers' => $answers,
                'unknown_is_yes' => $rule['unknown_counts_as_yes'],
                'routes' => [],
                'scan_found' => false,
            ];

            if ($this->holds($rule['when'], $context)) {
                $routes[] = $route;
            }
        }

        return $routes === [] ? [self::NO_ROUTE] : $routes;
    }

    /**
     * Whether a new phone package is advised.
     *
     * @param  bool  $scanFoundSomething  false when no scan was done
     */
    public function packageAdvised(array $answers, bool $scanFoundSomething): bool
    {
        $rule = $this->config['package'];

        return $this->holds($rule['when'], [
            'answers' => $answers,
            'unknown_is_yes' => $rule['unknown_counts_as_yes'],
            'routes' => $this->routes($answers),
            'scan_found' => $scanFoundSomething,
        ]);
    }

    /**
     * Whether the routes the participant reached are one of the accepted
     * outcomes for this scenario. Order does not matter.
     */
    public function matchesScenario(string $scenario, array $routes): bool
    {
        $accepted = $this->config['scenarios'][$scenario]['accepted_routes']
            ?? throw new InvalidArgumentException("Unknown scenario [{$scenario}].");

        sort($routes);

        foreach ($accepted as $set) {
            sort($set);

            if ($set === $routes) {
                return true;
            }
        }

        return false;
    }

    /**
     * Evaluates one condition from the config, recursively.
     */
    private function holds(array|string $condition, array $context): bool
    {
        if (is_array($condition)) {
            $parts = $condition['any'] ?? $condition['all']
                ?? throw new InvalidArgumentException('A condition group needs an "any" or "all" key.');

            $results = array_map(fn ($part) => $this->holds($part, $context), $parts);

            return isset($condition['any']) ? in_array(true, $results, true) : ! in_array(false, $results, true);
        }

        if (str_starts_with($condition, 'not:')) {
            return ! $this->holds(substr($condition, 4), $context);
        }

        if (str_starts_with($condition, 'route:')) {
            return in_array(substr($condition, 6), $context['routes'], true);
        }

        if ($condition === 'scan_found') {
            return $context['scan_found'];
        }

        $answer = $context['answers'][$condition]
            ?? throw new InvalidArgumentException("Unknown condition [{$condition}].");

        return $answer === self::YES || ($answer === self::UNKNOWN && $context['unknown_is_yes']);
    }

    /**
     * Every question must have exactly one valid answer. The screen enforces
     * this too, but the rules would give a wrong route on missing input.
     */
    private function assertComplete(array $answers): void
    {
        $questions = $this->config['questions'];

        $missing = array_diff($questions, array_keys($answers));
        $extra = array_diff(array_keys($answers), $questions);

        if ($missing !== [] || $extra !== []) {
            throw new InvalidArgumentException('Answers must cover exactly the questions: '.implode(', ', $questions).'.');
        }

        foreach ($answers as $question => $answer) {
            if (! in_array($answer, $this->config['answers'], true)) {
                throw new InvalidArgumentException("Invalid answer for [{$question}].");
            }
        }
    }
}
