{{-- /m/<key> for a module that is not merged yet. --}}
<div data-screen="coming-soon">
    <main class="mx-auto max-w-2xl px-4 pb-16 pt-8 sm:px-6 sm:pt-14">
        <p class="font-bold text-ink-soft">{{ __('demo.modules.'.$module.'.title') }}</p>
        <h1 id="screen-title" tabindex="-1" class="mt-2 text-[2rem] sm:text-[2.5rem]">{{ __('demo.coming_soon.title') }}</h1>
        <p class="mt-6 max-w-prose">{{ __('demo.coming_soon.lead') }}</p>
        <a href="{{ route('start') }}" class="btn-secondary mt-8">{{ __('demo.coming_soon.to_start') }}</a>
    </main>
</div>
