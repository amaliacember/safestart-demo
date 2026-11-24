@props(['steps', 'heading', 'label'])

{{--
    The case-file index: a heading (e.g. "Case 2 of 3") with "step X of N",
    plus the steps. Phones get a compact bar; wider screens a vertical list in
    the margin. Skipped steps say "not needed" instead of disappearing, so the
    count never jumps.

    steps:   list of ['label' => 'Quick check', 'state' => 'done|current|todo|skipped']
    heading: text before "step X of N", e.g. "Case 2 of 3" (may be empty)
    label:   name of the list for screen readers, e.g. "Steps in this case"
--}}
@php
    $current = collect($steps)->search(fn ($step) => $step['state'] === 'current');
@endphp

<nav aria-label="{{ $label }}">
    <p class="font-bold text-ink">
        {{ $heading }}
        @if ($current !== false)
            @php($stepText = __('demo.progress.step', ['n' => $current + 1, 'total' => count($steps)]))
            <span class="font-normal text-ink-soft">{{ $heading ? '– '.$stepText : Str::ucfirst($stepText) }}</span>
        @endif
    </p>

    {{-- Phone: one segment per step, the labels are for screen readers only. --}}
    {{-- No style="" here: the security policy blocks inline styles. --}}
    <ol class="mt-3 flex gap-1.5 lg:hidden">
        @foreach ($steps as $i => $step)
            <li @class([
                'h-2 flex-1 rounded-[2px]',
                'bg-pine' => in_array($step['state'], ['done', 'current']),
                'bg-line' => $step['state'] === 'todo',
                'border border-dashed border-edge bg-transparent' => $step['state'] === 'skipped',
            ])>
                <span class="sr-only">
                    {{ $i + 1 }}. {{ $step['label'] }}:
                    {{ match ($step['state']) {
                        'done' => __('demo.progress.done'),
                        'current' => __('demo.progress.current'),
                        'skipped' => __('demo.progress.not_needed'),
                        default => '',
                    } }}
                </span>
            </li>
        @endforeach
    </ol>

    {{-- Laptop: the index down the margin of the file. --}}
    <ol class="mt-5 hidden border-l-2 border-line lg:block">
        @foreach ($steps as $i => $step)
            <li
                @class([
                    '-ml-0.5 border-l-2 py-2 pl-4',
                    'border-pine' => $step['state'] === 'current',
                    'border-transparent' => $step['state'] !== 'current',
                ])
                @if ($step['state'] === 'current') aria-current="step" @endif
            >
                <span @class([
                    'flex items-baseline gap-3',
                    'font-bold text-ink' => $step['state'] === 'current',
                    'text-ink' => $step['state'] === 'done',
                    'text-ink-soft' => in_array($step['state'], ['todo', 'skipped']),
                ])>
                    <span class="w-4 shrink-0 text-right tabular-nums">{{ $i + 1 }}</span>
                    <span>
                        <span @class(['line-through decoration-edge' => $step['state'] === 'skipped'])>{{ $step['label'] }}</span>
                        @if ($step['state'] === 'skipped')
                            <span class="block text-[0.875rem] italic">{{ __('demo.progress.not_needed') }}</span>
                        @elseif ($step['state'] === 'done')
                            <span class="sr-only">({{ __('demo.progress.done') }})</span>
                        @endif
                    </span>
                </span>
            </li>
        @endforeach
    </ol>
</nav>
