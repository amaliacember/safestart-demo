{{-- Step 2: the six questions, all required. --}}
<h1 id="screen-title" tabindex="-1" class="text-[1.75rem] sm:text-[2rem]">{{ __('demo.check.title') }}</h1>
<p class="mt-4 max-w-prose">{{ __('demo.check.lead') }}</p>

<form wire:submit="submitCheck" class="mt-6" novalidate>
    @if ($errors->any())
        <p role="alert" class="mb-2 border-l-4 border-alert bg-alert-wash px-4 py-3 font-bold text-alert">
            {{ __('demo.check.errors') }}
        </p>
    @endif

    @foreach (config('safestart.questions') as $i => $question)
        <x-tri-choice :question="$question" :number="$i + 1" wire:key="q-{{ $question }}" />
    @endforeach

    <x-actions>
        <button type="submit" class="btn-primary">{{ __('demo.check.next') }}</button>
    </x-actions>
</form>
