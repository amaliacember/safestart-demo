{{-- Step 6: order the package (nothing is ordered). Prices come from config. --}}
@php($pricing = config('safestart.pricing'))

<h1 id="screen-title" tabindex="-1" class="text-[1.75rem] sm:text-[2rem]">{{ __('demo.order.title') }}</h1>
<p class="mt-4 max-w-prose">{{ __('demo.order.lead') }}</p>

<dl class="mt-6 divide-y divide-line border-y border-line">
    <div class="py-3 sm:grid sm:grid-cols-[10rem_1fr] sm:gap-4">
        <dt class="font-bold">{{ __('demo.order.price_label') }}</dt>
        <dd class="tabular-nums">
            {{ __('demo.order.price', [
                'package' => number_format($pricing['package_eur'], 0, ',', '.'),
                'subscription' => number_format($pricing['subscription_eur_per_year'], 0, ',', '.'),
            ]) }}
        </dd>
    </div>
    <div class="py-3 sm:grid sm:grid-cols-[10rem_1fr] sm:gap-4">
        <dt class="font-bold">{{ __('demo.order.delivery_label') }}</dt>
        <dd>{{ __('demo.order.delivery', ['days' => $pricing['delivery_working_days']]) }}</dd>
    </div>
    <div class="py-3 sm:grid sm:grid-cols-[10rem_1fr] sm:gap-4">
        <dt class="font-bold">{{ __('demo.order.funding_label') }}</dt>
        <dd>{{ __('demo.order.funding') }}</dd>
    </div>
</dl>

<x-actions>
    <button type="button" wire:click="orderPackage" class="btn-primary">{{ __('demo.order.submit') }}</button>
    <button type="button" wire:click="closeWithoutOrder" class="btn-quiet">{{ __('demo.order.skip') }}</button>
</x-actions>
