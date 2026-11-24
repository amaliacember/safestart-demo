import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

// Fonts come from @fontsource packages imported in app.css, so Vite bundles
// them into public/build. No font CDN is ever contacted.
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // An IPv4 address on purpose: the Content-Security-Policy cannot list
        // IPv6 addresses like [::1], so the dev server would be blocked.
        host: '127.0.0.1',
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
