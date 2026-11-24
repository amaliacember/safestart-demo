{{-- Step 2: the six questions, all required. --}}
<h1 id="screen-title" tabindex="-1" class="text-[1.75rem] sm:text-[2rem]">{{ __('caseworker.check.title') }}</h1>
<p class="mt-4 max-w-prose">{{ __('caseworker.check.lead') }}</p>

<form wire:submit="submitCheck" class="mt-6" novalidate>
    @if ($errors->any())
        <p role="alert" class="mb-2 border-l-4 border-alert bg-alert-wash px-4 py-3 font-bold text-alert">
            {{ __('caseworker.check.errors') }}
        </p>
    @endif

    @php($options = collect(config('safestart.caseworker.answers'))->mapWithKeys(fn ($answer) => [$answer => __('caseworker.answers.'.$answer)])->all())
    @foreach (config('safestart.caseworker.questions') as $i => $question)
        <x-tri-choice
            :question="$question"
            :number="$i + 1"
            :label="__('caseworker.questions.'.$question)"
            :options="$options"
            wire:key="q-{{ $question }}"
        />
    @endforeach

    <x-actions>
        <button type="submit" class="btn-primary">{{ __('caseworker.check.next') }}</button>
    </x-actions>
</form>
