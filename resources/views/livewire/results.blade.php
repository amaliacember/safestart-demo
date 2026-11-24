{{--
    The shared results screen. One block per module done in this session,
    then copy / download / close. Shared file.

    Column names and notes can be replaced per module: ResultsText::term()
    looks for "<module>.results.<key>" before "demo.results.<key>".
--}}
@use('App\Support\Modules')
@use('App\Support\ResultsText')
@php
    $fmt = fn ($ms) => ResultsText::duration($ms);
    $toneClass = fn ($tone) => match ($tone) {
        'good' => 'text-pine',
        'warn' => 'text-amber-ink',
        'bad' => 'text-alert',
        default => 'text-ink',
    };
@endphp

<div data-screen="results">
    <main class="mx-auto max-w-3xl px-4 pb-16 pt-8 sm:px-6 sm:pt-14">
        <h1 id="screen-title" tabindex="-1" class="text-[2rem] sm:text-[2.5rem]">{{ __('demo.results.title') }}</h1>

        @if ($results['modules'] === [])
            <h2 class="mt-6 text-[1.25rem]">{{ __('demo.results.empty_title') }}</h2>
            <p class="mt-2 max-w-prose">{{ __('demo.results.empty_lead') }}</p>
            <a href="{{ route('start') }}" class="btn-secondary mt-8">{{ __('demo.results.to_start') }}</a>
        @else
            <p class="mt-4 max-w-prose">{{ __('demo.results.lead') }}</p>

            @foreach ($results['modules'] as $summary)
                @php
                    $key = $summary['module'];
                    $class = Modules::classFor($key);
                    $waits = $class::waits();
                    $tones = $class::outcomes();
                    $term = fn ($name, $replace = []) => ResultsText::term($key, $name, $replace);
                @endphp

                <section class="mt-10" wire:key="results-{{ $key }}">
                    <h2 class="text-[1.375rem] sm:text-[1.5rem]">{{ ResultsText::moduleTitle($key) }}</h2>

                    {{-- Phones: one block per task, so no column is cut off. --}}
                    <div class="mt-4 rounded-[var(--radius-sheet)] border-2 border-ink bg-sheet sm:hidden">
                        <ul class="divide-y divide-line">
                            @foreach ($summary['tasks'] as $task)
                                <li class="px-4 py-4">
                                    <div class="flex items-baseline justify-between gap-3">
                                        <span class="font-serif text-[1.125rem] font-semibold">{{ $class::taskLabel($task['task']) }}</span>
                                        @if (! $task['finished'])
                                            <span class="italic text-ink-soft">{{ $term('unfinished') }}</span>
                                        @elseif ($task['outcome'] !== null)
                                            <span class="font-bold {{ $toneClass($tones[$task['outcome']] ?? null) }}">{{ __($key.'.outcomes.'.$task['outcome']) }}</span>
                                        @endif
                                    </div>
                                    @if ($task['finished'])
                                        <dl @class(['mt-2 grid gap-2 text-[0.9375rem] tabular-nums', 'grid-cols-3' => $waits, 'grid-cols-2' => ! $waits])>
                                            <div><dt class="text-ink-soft">{{ $term('col_time') }}</dt><dd class="font-bold">{{ $fmt($task['time_ms']) }}</dd></div>
                                            @if ($waits)
                                                <div><dt class="text-ink-soft">{{ $term('col_without_wait') }}</dt><dd class="font-bold">{{ $fmt($task['time_without_wait_ms']) }}</dd></div>
                                            @endif
                                            <div><dt class="text-ink-soft">{{ $term('col_back') }}</dt><dd class="font-bold">{{ $task['back_clicks'] }}</dd></div>
                                        </dl>
                                    @endif
                                </li>
                            @endforeach
                            <li class="px-4 py-4">
                                <span class="font-bold">{{ $term('total') }}</span>
                                <dl @class(['mt-2 grid gap-2 text-[0.9375rem] tabular-nums', 'grid-cols-3' => $waits, 'grid-cols-2' => ! $waits])>
                                    <div><dt class="text-ink-soft">{{ $term('col_time') }}</dt><dd class="font-bold">{{ $fmt($summary['total_time_ms']) }}</dd></div>
                                    @if ($waits)
                                        <div><dt class="text-ink-soft">{{ $term('col_without_wait') }}</dt><dd class="font-bold">{{ $fmt($summary['total_time_without_wait_ms']) }}</dd></div>
                                    @endif
                                    <div><dt class="text-ink-soft">{{ $term('col_back') }}</dt><dd class="font-bold">{{ $summary['total_back_clicks'] }}</dd></div>
                                </dl>
                                @if ($summary['outcomes'] !== [])
                                    <p class="mt-2">{{ ResultsText::outcomeList($key, $summary['outcomes']) }}</p>
                                @endif
                            </li>
                        </ul>
                    </div>

                    {{-- Wider screens: a table. --}}
                    <div class="mt-4 hidden rounded-[var(--radius-sheet)] border-2 border-ink bg-sheet sm:block">
                        <table class="w-full text-left tabular-nums">
                            <caption class="sr-only">{{ ResultsText::moduleTitle($key) }}</caption>
                            <thead class="border-b-2 border-ink">
                                <tr>
                                    <th scope="col" class="px-4 py-3">{{ $term('col_task') }}</th>
                                    <th scope="col" class="px-4 py-3 text-right">{{ $term('col_time') }}</th>
                                    @if ($waits)
                                        <th scope="col" class="px-4 py-3 text-right">{{ $term('col_without_wait') }}</th>
                                    @endif
                                    <th scope="col" class="px-4 py-3">{{ $term('col_outcome') }}</th>
                                    <th scope="col" class="px-4 py-3 text-right">{{ $term('col_back') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach ($summary['tasks'] as $task)
                                    <tr>
                                        <th scope="row" class="px-4 py-3 font-serif font-semibold">{{ $class::taskLabel($task['task']) }}</th>
                                        @if ($task['finished'])
                                            <td class="px-4 py-3 text-right">{{ $fmt($task['time_ms']) }}</td>
                                            @if ($waits)
                                                <td class="px-4 py-3 text-right">{{ $fmt($task['time_without_wait_ms']) }}</td>
                                            @endif
                                            <td class="px-4 py-3">
                                                @if ($task['outcome'] !== null)
                                                    <span class="font-bold {{ $toneClass($tones[$task['outcome']] ?? null) }}">{{ __($key.'.outcomes.'.$task['outcome']) }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right">{{ $task['back_clicks'] }}</td>
                                        @else
                                            <td colspan="{{ $waits ? 4 : 3 }}" class="px-4 py-3 italic text-ink-soft">{{ $term('unfinished') }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t-2 border-ink font-bold">
                                <tr>
                                    <th scope="row" class="px-4 py-3">{{ $term('total') }}</th>
                                    <td class="px-4 py-3 text-right">{{ $fmt($summary['total_time_ms']) }}</td>
                                    @if ($waits)
                                        <td class="px-4 py-3 text-right">{{ $fmt($summary['total_time_without_wait_ms']) }}</td>
                                    @endif
                                    <td class="px-4 py-3 font-normal">{{ ResultsText::outcomeList($key, $summary['outcomes']) }}</td>
                                    <td class="px-4 py-3 text-right">{{ $summary['total_back_clicks'] }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <p class="mt-3 text-[0.9375rem] text-ink-soft">{{ $term('times_note') }}</p>
                    @if ($summary['target_ms'] !== null)
                        <p class="mt-4 font-bold">{{ ResultsText::target($key, $summary) }}</p>
                    @endif
                </section>
            @endforeach

            {{-- Copy: the plain text is visible, so it can also be selected by hand. --}}
            <div
                x-data="copyResults"
                data-copied="{{ __('demo.results.copied') }}"
                data-failed="{{ __('demo.results.copy_failed') }}"
                class="mt-10 border-t-2 border-ink pt-8"
            >
                <label for="results-text" class="field-label">{{ __('demo.results.text_label') }}</label>
                <textarea
                    id="results-text"
                    x-ref="text"
                    readonly
                    rows="12"
                    class="mt-2 block w-full rounded-[var(--radius-control)] border-2 border-edge bg-sheet p-3 font-sans text-[0.9375rem] leading-normal text-ink"
                >{{ $resultsText }}</textarea>

                <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                    <button type="button" @click="copy" class="btn-primary">{{ __('demo.results.copy') }}</button>
                    <a href="{{ route('results.download') }}" class="btn-secondary" download>{{ __('demo.results.download') }}</a>
                </div>
                <p role="status" class="mt-3 min-h-6 font-bold text-pine" x-text="message"></p>
            </div>

            <div class="mt-8 flex flex-col gap-6 border-t border-line pt-6 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <button type="button" wire:click="closeSession" class="btn-quiet -ml-2">{{ __('demo.results.close') }}</button>
                    <p class="max-w-sm text-[0.9375rem] text-ink-soft">{{ __('demo.results.close_note') }}</p>
                </div>
                <a href="{{ route('start') }}" class="btn-quiet -ml-2 self-start sm:ml-0">{{ __('demo.results.other_module') }}</a>
            </div>
        @endif
    </main>
</div>
