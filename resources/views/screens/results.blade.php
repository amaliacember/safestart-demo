{{-- Results: per case and total, then copy / download / close. --}}
@php($fmt = fn ($ms) => \App\Support\ResultsText::duration($ms))

<h1 id="screen-title" tabindex="-1" class="text-[2rem] sm:text-[2.5rem]">{{ __('demo.results.title') }}</h1>
<p class="mt-4 max-w-prose">{{ __('demo.results.lead') }}</p>

{{-- Phones: one block per case, so no column is cut off. --}}
<div class="mt-8 rounded-[var(--radius-sheet)] border-2 border-ink bg-sheet sm:hidden">
    <ul class="divide-y divide-line">
        @foreach ($summary['cases'] as $case)
            <li class="px-4 py-4">
                <div class="flex items-baseline justify-between gap-3">
                    <span class="font-serif text-[1.125rem] font-semibold">
                        {{ $case['scenario'] }} <span class="font-normal">{{ __('demo.scenarios.'.$case['scenario'].'.name') }}</span>
                    </span>
                    @if ($case['finished'])
                        <span @class([
                            'font-bold',
                            'text-pine' => $case['route_outcome'] === 'exact',
                            'text-amber-ink' => $case['route_outcome'] === 'extra',
                            'text-alert' => $case['route_outcome'] === 'missed',
                        ])>{{ __('demo.outcomes.'.$case['route_outcome']) }}</span>
                    @else
                        <span class="italic text-ink-soft">{{ __('demo.results.unfinished') }}</span>
                    @endif
                </div>
                @if ($case['finished'])
                    <dl class="mt-2 grid grid-cols-3 gap-2 text-[0.9375rem] tabular-nums">
                        <div><dt class="text-ink-soft">{{ __('demo.results.col_time') }}</dt><dd class="font-bold">{{ $fmt($case['time_ms']) }}</dd></div>
                        <div><dt class="text-ink-soft">{{ __('demo.results.col_without_scan') }}</dt><dd class="font-bold">{{ $fmt($case['time_without_scan_ms']) }}</dd></div>
                        <div><dt class="text-ink-soft">{{ __('demo.results.col_back') }}</dt><dd class="font-bold">{{ $case['back_clicks'] }}</dd></div>
                    </dl>
                @endif
            </li>
        @endforeach
        <li class="px-4 py-4">
            <span class="font-bold">{{ __('demo.results.total') }}</span>
            <dl class="mt-2 grid grid-cols-3 gap-2 text-[0.9375rem] tabular-nums">
                <div><dt class="text-ink-soft">{{ __('demo.results.col_time') }}</dt><dd class="font-bold">{{ $fmt($summary['total_time_ms']) }}</dd></div>
                <div><dt class="text-ink-soft">{{ __('demo.results.col_without_scan') }}</dt><dd class="font-bold">{{ $fmt($summary['total_time_without_scan_ms']) }}</dd></div>
                <div><dt class="text-ink-soft">{{ __('demo.results.col_back') }}</dt><dd class="font-bold">{{ $summary['total_back_clicks'] }}</dd></div>
            </dl>
            <p class="mt-2">{{ __('demo.results.outcomes_line', $summary['route_outcomes']) }}</p>
        </li>
    </ul>
</div>

{{-- Wider screens: a table. --}}
<div class="mt-8 hidden rounded-[var(--radius-sheet)] border-2 border-ink bg-sheet sm:block">
    <table class="w-full text-left tabular-nums">
        <caption class="sr-only">{{ __('demo.results.title') }}</caption>
        <thead class="border-b-2 border-ink">
            <tr>
                <th scope="col" class="px-4 py-3">{{ __('demo.results.col_case') }}</th>
                <th scope="col" class="px-4 py-3 text-right">{{ __('demo.results.col_time') }}</th>
                <th scope="col" class="px-4 py-3 text-right">{{ __('demo.results.col_without_scan') }}</th>
                <th scope="col" class="px-4 py-3">{{ __('demo.results.col_outcome') }}</th>
                <th scope="col" class="px-4 py-3 text-right">{{ __('demo.results.col_back') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line">
            @foreach ($summary['cases'] as $case)
                <tr>
                    <th scope="row" class="px-4 py-3 font-serif font-semibold">
                        {{ $case['scenario'] }} <span class="font-normal">{{ __('demo.scenarios.'.$case['scenario'].'.name') }}</span>
                    </th>
                    @if ($case['finished'])
                        <td class="px-4 py-3 text-right">{{ $fmt($case['time_ms']) }}</td>
                        <td class="px-4 py-3 text-right">{{ $fmt($case['time_without_scan_ms']) }}</td>
                        <td class="px-4 py-3">
                            <span @class([
                                'font-bold',
                                'text-pine' => $case['route_outcome'] === 'exact',
                                'text-amber-ink' => $case['route_outcome'] === 'extra',
                                'text-alert' => $case['route_outcome'] === 'missed',
                            ])>{{ __('demo.outcomes.'.$case['route_outcome']) }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">{{ $case['back_clicks'] }}</td>
                    @else
                        <td colspan="4" class="px-4 py-3 italic text-ink-soft">{{ __('demo.results.unfinished') }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
        <tfoot class="border-t-2 border-ink font-bold">
            <tr>
                <th scope="row" class="px-4 py-3">{{ __('demo.results.total') }}</th>
                <td class="px-4 py-3 text-right">{{ $fmt($summary['total_time_ms']) }}</td>
                <td class="px-4 py-3 text-right">{{ $fmt($summary['total_time_without_scan_ms']) }}</td>
                <td class="px-4 py-3 font-normal">{{ __('demo.results.outcomes_line', $summary['route_outcomes']) }}</td>
                <td class="px-4 py-3 text-right">{{ $summary['total_back_clicks'] }}</td>
            </tr>
        </tfoot>
    </table>
</div>

<p class="mt-3 text-[0.9375rem] text-ink-soft">{{ __('demo.results.times_note') }}</p>
<p class="mt-4 font-bold">
    {{ __('demo.results.target', [
        'minutes' => config('safestart.timing.target_total_minutes'),
        'answer' => $summary['under_time_target'] ? __('demo.results.yes') : __('demo.results.no'),
    ]) }}
</p>

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

<div class="mt-8 border-t border-line pt-6">
    <button type="button" wire:click="closeSession" class="btn-quiet -ml-2">{{ __('demo.results.close') }}</button>
    <p class="text-[0.9375rem] text-ink-soft">{{ __('demo.results.close_note') }}</p>
</div>
