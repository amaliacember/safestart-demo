{{-- Step 3: which route(s) apply, one line why each. --}}
<h1 id="screen-title" tabindex="-1" class="text-[1.75rem] sm:text-[2rem]">{{ __('caseworker.route.title') }}</h1>
<p class="mt-4 max-w-prose">{{ trans_choice('caseworker.route.lead', count($routes)) }}</p>

<ul class="mt-6 space-y-3">
    @foreach ($routes as $route)
        <li @class([
            'border-l-4 py-1 pl-4',
            'border-amber' => $route === 'scan',
            'border-pine' => $route !== 'scan',
        ])>
            <h2 class="text-[1.25rem]">{{ __('caseworker.routes.'.$route.'.title') }}</h2>
            <p class="mt-1 text-ink-soft">{{ __('caseworker.routes.'.$route.'.why') }}</p>
        </li>
    @endforeach
</ul>

<x-actions>
    @if (in_array('scan', $routes, true))
        <button type="button" wire:click="continueFromRoute" class="btn-primary">{{ __('caseworker.route.next_scan') }}</button>
    @else
        <button type="button" wire:click="continueFromRoute" class="btn-primary">{{ __('caseworker.route.next_advice') }}</button>
    @endif
</x-actions>
