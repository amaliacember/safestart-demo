{{-- Step 4: the fake scan. Connect -> progress bar (skippable) -> scripted result. --}}
<h1 id="screen-title" tabindex="-1" class="text-[1.75rem] sm:text-[2rem]">{{ __('demo.scan.title') }}</h1>

<div aria-live="polite">
    @if ($scanState === 'idle')
        <p class="mt-4 max-w-prose">{{ __('demo.scan.connect_lead') }}</p>
        <p class="mt-3 max-w-prose text-ink-soft">{{ __('demo.scan.connect_note') }}</p>

        <x-actions>
            <button type="button" wire:click="startScan" class="btn-primary">{{ __('demo.scan.start') }}</button>
        </x-actions>
    @elseif ($scanState === 'running')
        <div x-data="scanProgress" data-duration="{{ config('safestart.timing.scan_animation_ms') }}" class="mt-6" wire:key="scan-running">
            <p id="scan-progress-label" class="font-bold">{{ __('demo.scan.running') }}</p>
            <div
                x-ref="track"
                role="progressbar"
                aria-labelledby="scan-progress-label"
                aria-valuemin="0"
                aria-valuemax="100"
                aria-valuenow="0"
                class="mt-3 h-4 overflow-hidden rounded-[2px] border-2 border-ink bg-sheet"
            >
                <div x-ref="bar" class="h-full w-0 bg-pine"></div>
            </div>

            <x-actions>
                <button type="button" @click="skip" class="btn-secondary">{{ __('demo.scan.skip') }}</button>
            </x-actions>
        </div>
    @else
        @if ($scanResult['found'])
            <div class="mt-6 border-l-4 border-amber bg-amber-wash px-4 py-4">
                <h2 class="text-[1.25rem]">{{ __('demo.scan.found_title') }}</h2>
                <p class="mt-1">{{ __('demo.scan.found_lead') }}</p>
            </div>

            <ul class="mt-4 divide-y divide-line border-y border-line">
                @foreach ($scanResult['apps'] as $app)
                    <li class="py-3">
                        <span class="font-bold">{{ $app['name'] }}</span>
                        <span class="block text-ink-soft">{{ __('demo.findings.'.$app['finding']) }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="mt-6 border-l-4 border-pine bg-pine-wash px-4 py-4">
                <h2 class="text-[1.25rem]">{{ __('demo.scan.none_title') }}</h2>
                <p class="mt-1">{{ __('demo.scan.none_lead') }}</p>
            </div>
        @endif

        <p class="mt-4 text-[0.9375rem] italic text-ink-soft">{{ __('demo.scan.scripted') }}</p>

        <x-actions>
            <button type="button" wire:click="continueFromScan" class="btn-primary">{{ __('demo.scan.next') }}</button>
        </x-actions>
    @endif
</div>
