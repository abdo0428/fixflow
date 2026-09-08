<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), config('app.rtl_locales', []), true) ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'FixFlow') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="flex min-h-screen flex-col items-center bg-slate-50 px-4 pt-6 sm:justify-center sm:pt-0">
            <div class="absolute top-4 end-4">
                <x-ui.locale-switcher compact />
            </div>

            <div>
                <a href="/" class="flex flex-col items-center gap-2">
                    <x-application-logo class="h-12 w-12 fill-current text-cyan-700" />
                    <span class="text-lg font-bold text-slate-950">FixFlow</span>
                </a>
            </div>

            <div class="ff-card mt-6 w-full max-w-md overflow-hidden p-6">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
