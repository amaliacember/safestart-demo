<?php

use Tests\TestCase;

/*
| Feature tests boot the Laravel app. Unit tests stay plain PHP: they load
| a module's config file directly (caseworkerConfig()), so they test the
| real rules without starting the framework.
*/

pest()->extend(TestCase::class)->in('Feature');

/** config/safestart/caseworker.php, loaded without the framework. */
function caseworkerConfig(): array
{
    return require __DIR__.'/../config/safestart/caseworker.php';
}

/**
 * Module 1: builds a full answer set: everything "no" unless overridden.
 * Example: answers(q2: 'yes', q4: 'unknown')
 */
function answers(string ...$overrides): array
{
    return array_merge(
        ['q1' => 'no', 'q2' => 'no', 'q3' => 'no', 'q4' => 'no', 'q5' => 'no', 'q6' => 'no'],
        $overrides,
    );
}
