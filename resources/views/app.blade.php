<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'BeatWager') }}</title>

        <!-- Default Open Graph / Social Meta Tags (can be overridden by Inertia pages) -->
        <meta property="og:site_name" content="WagerBot">
        <meta property="og:url" content="{{ config('app.url') }}">
        <meta property="og:image" content="{{ config('app.url') }}/media/wagerbot-lg.png">
        <meta name="theme-color" content="#5da7f8">

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="/media/wagerbot-lg.png">
        <link rel="apple-touch-icon" href="/media/wagerbot-lg.png">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
