@props(['steps', 'currentStep', 'caseIndex', 'caseCount'])

{{--
    The case-file index: "Case 2 of 3 – step 3 of 6" plus the six steps.
    Phones get a compact six-part bar; wider screens a vertical list in the
    margin. Skipped steps say "not needed" instead of disappearing, so the
    count never jumps.
--}}
<nav aria-label="{{ __('demo.progress.steps_label') }}">
    <p class="font-bold text-ink">
        {{ __('demo.progress.case', ['n' => $caseIndex, 'total' => $caseCount]) }}
        <span class="font-normal text-ink-soft">– {{ __('demo.progress.step', ['n' => $currentStep, 'total' => count($steps)]) }}</span>
    </p>

    {{-- Phone: six segments, the labels are for screen readers only. --}}
    <ol class="mt-3 grid grid-cols-6 gap-1.5 lg:hidden">
        @foreach ($steps as $step)
            <li @class([
                'h-2 rounded-[2px]',
                'bg-pine' => in_array($step['state'], ['done', 'current']),
                'bg-line' => $step['state'] === 'todo',
                'border border-dashed border-edge bg-transparent' => $step['state'] === 'skipped',
            ])>
                <span class="sr-only">
                    {{ $step['number'] }}. {{ __('demo.steps.'.$step['key']) }}:
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
        @foreach ($steps as $step)
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
                    <span class="w-4 shrink-0 text-right tabular-nums">{{ $step['number'] }}</span>
                    <span>
                        <span @class(['line-through decoration-edge' => $step['state'] === 'skipped'])>{{ __('demo.steps.'.$step['key']) }}</span>
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
