<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), config('app.rtl_locales', []), true) ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>FixFlow</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
        <main class="mx-auto flex min-h-screen max-w-6xl flex-col px-6 py-8">
            <nav class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <x-application-logo class="h-10 w-10 fill-current text-cyan-700" />
                    <div>
                        <div class="text-xl font-bold tracking-normal text-slate-950">{{ __('ui.common.app_name') }}</div>
                        <div class="text-sm text-slate-500">{{ __('ui.welcome.eyebrow') }}</div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <x-ui.locale-switcher compact />

                    @auth
                        <x-ui.button :href="route('dashboard')">{{ __('ui.welcome.open_dashboard') }}</x-ui.button>
                    @else
                        <x-ui.button :href="route('login')" variant="secondary">{{ __('ui.actions.login') }}</x-ui.button>
                    @endauth
                </div>
            </nav>

            <section class="grid flex-1 items-center gap-10 py-12 lg:grid-cols-[1.1fr_0.9fr]">
                <div>
                    <x-ui.badge variant="primary">{{ __('ui.welcome.eyebrow') }}</x-ui.badge>
                    <h1 class="mt-4 max-w-3xl text-4xl font-bold leading-tight tracking-normal text-slate-950 sm:text-5xl">
                        {{ __('ui.welcome.headline') }}
                    </h1>
                    <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">
                        {{ __('ui.welcome.subtitle') }}
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <x-ui.button :href="route('login')" size="lg">{{ __('ui.welcome.try_login') }}</x-ui.button>
                    </div>
                </div>

                <x-ui.card>
                    <h2 class="text-lg font-semibold text-slate-950">{{ __('ui.welcome.demo_accounts') }}</h2>
                    <div class="mt-5 space-y-3 text-sm">
                        @foreach ([
                            'super@example.com' => 'Super Admin',
                            'admin@example.com' => 'Company Admin',
                            'dispatcher@example.com' => 'Dispatcher',
                            'technician@example.com' => 'Technician',
                            'customer@example.com' => 'Customer',
                        ] as $email => $role)
                            <div class="flex items-center justify-between gap-4 rounded-md bg-slate-50 px-4 py-3">
                                <span class="font-medium text-slate-800">{{ $role }}</span>
                                <span class="font-mono text-xs text-slate-600">{{ $email }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-4 text-sm text-slate-500">{{ __('ui.welcome.demo_password') }}: <span class="font-mono">password</span></p>
                </x-ui.card>
            </section>
        </main>
    </body>
</html>
