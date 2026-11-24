/*
 * Small client-side helpers.
 *
 * Livewire ships Alpine in its CSP-safe build (config/livewire.php, csp_safe).
 * That build cannot run inline JavaScript from HTML attributes, so every bit of
 * behaviour lives in a named Alpine.data() component here, and the Blade views
 * only refer to names like x-data="scanProgress" and @click="skip".
 */

document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;

    /*
     * Fake scan progress bar. Runs for data-duration ms, then tells the
     * server it is done. "Skip" ends it early. The server times the
     * animation itself; the browser's clock is not used for results.
     */
    Alpine.data('scanProgress', () => ({
        finished: false,

        init() {
            const duration = Number(this.$el.dataset.duration) || 3500;
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const started = performance.now();

            const tick = (now) => {
                if (this.finished) {
                    return;
                }

                let percent = Math.min(100, ((now - started) / duration) * 100);

                // With reduced motion, move in four calm steps instead of a smooth slide.
                if (reduceMotion) {
                    percent = Math.floor(percent / 25) * 25;
                }

                this.show(percent);

                if (now - started >= duration) {
                    this.finish();
                } else {
                    requestAnimationFrame(tick);
                }
            };

            requestAnimationFrame(tick);
        },

        show(percent) {
            const rounded = Math.round(percent);
            this.$refs.bar.style.width = `${rounded}%`;
            this.$refs.track.setAttribute('aria-valuenow', String(rounded));
        },

        skip() {
            this.finish();
        },

        finish() {
            if (this.finished) {
                return;
            }

            this.finished = true;
            this.show(100);
            this.$wire.finishScan();
        },
    }));

    /*
     * "Copy results". Uses the Clipboard API when the page is on HTTPS;
     * on a laptop's plain http:// it falls back to selecting the text and
     * the older copy command.
     */
    Alpine.data('copyResults', () => ({
        message: '',

        async copy() {
            const field = this.$refs.text;
            let ok = false;

            try {
                await navigator.clipboard.writeText(field.value);
                ok = true;
            } catch {
                field.focus();
                field.select();
                ok = document.execCommand('copy');
            }

            this.message = ok ? this.$root.dataset.copied : this.$root.dataset.failed;
        },
    }));
});

/*
 * After Livewire swaps to a new screen: scroll to the top and move keyboard
 * and screen-reader focus to the new heading, so nobody is left halfway down
 * a page that no longer exists.
 */
document.addEventListener('livewire:init', () => {
    let lastScreen = document.querySelector('[data-screen]')?.dataset.screen;

    window.Livewire.hook('commit', ({ succeed }) => {
        succeed(() => {
            requestAnimationFrame(() => {
                const screen = document.querySelector('[data-screen]')?.dataset.screen;

                if (screen && screen !== lastScreen) {
                    lastScreen = screen;
                    window.scrollTo(0, 0);
                    document.querySelector('h1[tabindex="-1"]')?.focus({ preventScroll: true });
                }
            });
        });
    });
});
