{{-- Step 6: order the package (nothing is ordered). Prices come from config. --}}
@php($pricing = config('safestart.caseworker.pricing'))

<h1 id="screen-title" tabindex="-1" class="text-[1.75rem] sm:text-[2rem]">{{ __('caseworker.order.title') }}</h1>
<p class="mt-4 max-w-prose">{{ __('caseworker.order.lead') }}</p>

<dl class="mt-6 divide-y divide-line border-t border-line">
    <div class="py-3 sm:grid sm:grid-cols-[10rem_1fr] sm:gap-4">
        <dt class="font-bold">{{ __('caseworker.order.price_label') }}</dt>
        <dd class="tabular-nums">
            {{ __('caseworker.order.price', [
                'package' => number_format($pricing['package_eur'], 0, ',', '.'),
                'subscription' => number_format($pricing['subscription_eur_per_year'], 0, ',', '.'),
            ]) }}
        </dd>
    </div>
    <div class="py-3 sm:grid sm:grid-cols-[10rem_1fr] sm:gap-4">
        <dt class="font-bold">{{ __('caseworker.order.delivery_label') }}</dt>
        <dd>{{ __('caseworker.order.delivery', ['days' => $pricing['delivery_working_days']]) }}</dd>
    </div>
    <div class="py-3 sm:grid sm:grid-cols-[10rem_1fr] sm:gap-4">
        <dt class="font-bold">{{ __('caseworker.order.funding_label') }}</dt>
        <dd>{{ __('caseworker.order.funding') }}</dd>
    </div>
</dl>

<x-actions>
    <button type="button" wire:click="orderPackage" class="btn-primary">{{ __('caseworker.order.submit') }}</button>
</x-actions>

{{-- The way out without ordering: below the bar, so the main action stays clear. --}}
<div class="mt-2 flex sm:justify-end">
    <button type="button" wire:click="closeWithoutOrder" class="btn-quiet -ml-2 sm:-mr-2 sm:ml-0">{{ __('caseworker.order.skip') }}</button>
</div>
