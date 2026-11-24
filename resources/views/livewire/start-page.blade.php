{{-- Start page: language, what SafeStart is, and the five modules. --}}
<div data-screen="start">
    <main class="mx-auto max-w-2xl px-4 pb-16 pt-8 sm:px-6 sm:pt-14">
        <x-language-toggle />

        <h1 id="screen-title" tabindex="-1" class="mt-4 text-[2rem] sm:text-[2.5rem]">{{ __('demo.start.title') }}</h1>

        <p class="mt-6 max-w-prose font-serif text-[1.3125rem] leading-snug">{{ __('demo.start.lead') }}</p>

        <p class="mt-6 max-w-prose border-l-4 border-amber bg-amber-wash px-4 py-3">{{ __('demo.start.privacy') }}</p>

        {{-- The modules as the index of a file: number, title, one line. --}}
        <nav aria-label="{{ __('demo.start.modules_label') }}" class="mt-10">
            <ol class="divide-y divide-line rounded-[var(--radius-sheet)] border-2 border-ink bg-sheet">
                @foreach ($modules as $module)
                    <li class="flex gap-4 px-4 py-4 sm:px-6">
                        <span class="w-6 shrink-0 font-serif text-[1.25rem] text-ink-soft tabular-nums" aria-hidden="true">{{ $module['number'] }}</span>
                        <div class="min-w-0">
                            <span class="sr-only">{{ __('demo.start.module_number', ['n' => $module['number']]) }}:</span>
                            @if ($module['available'])
                                <a href="{{ route('module.'.$module['key']) }}" class="font-serif text-[1.25rem] font-semibold text-pine underline decoration-2 underline-offset-4 hover:text-pine-deep">
                                    {{ __('demo.modules.'.$module['key'].'.title') }}
                                </a>
                            @else
                                <span class="font-serif text-[1.25rem] font-semibold text-ink-soft">{{ __('demo.modules.'.$module['key'].'.title') }}</span>
                                <span class="ml-2 inline-block border border-edge px-2 text-[0.875rem] font-bold text-ink-soft">{{ __('demo.start.coming_soon') }}</span>
                            @endif
                            <p class="mt-1 text-ink-soft">{{ __('demo.modules.'.$module['key'].'.summary') }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </nav>
    </main>
</div>
