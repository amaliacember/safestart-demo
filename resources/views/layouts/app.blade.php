<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="no-referrer">
    <title>{{ $title ?? __('demo.app_title') }}</title>
    {{-- Vite adds this request's CSP nonce to these tags itself. The meta tag
         lets the dev server (npm run dev) tag the styles it injects too. --}}
    <meta property="csp-nonce" nonce="{{ Vite::cspNonce() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh">
    <x-banner />

    {{ $slot }}
</body>
</html>
