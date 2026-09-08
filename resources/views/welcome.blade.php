<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>FixFlow</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
        <main class="mx-auto flex min-h-screen max-w-6xl flex-col px-6 py-8">
            <nav class="flex items-center justify-between">
                <div>
                    <div class="text-xl font-bold tracking-normal text-gray-950">FixFlow</div>
                    <div class="text-sm text-gray-500">Maintenance and Warranty Management</div>
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                            لوحة التحكم
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-white">
                            تسجيل الدخول
                        </a>
                    @endauth
                </div>
            </nav>

            <section class="grid flex-1 items-center gap-10 py-12 lg:grid-cols-[1.1fr_0.9fr]">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-indigo-700">Laravel Portfolio Project</p>
                    <h1 class="mt-4 max-w-3xl text-4xl font-bold leading-tight tracking-normal text-gray-950 sm:text-5xl">
                        نظام لإدارة طلبات الصيانة والضمان والزيارات الميدانية.
                    </h1>
                    <p class="mt-5 max-w-2xl text-lg leading-8 text-gray-600">
                        يجمع FixFlow بين إدارة الشركات والعملاء والأجهزة والفنيين وقطع الغيار والتقارير والفواتير داخل تجربة واحدة مبنية بـ Laravel.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('login') }}" class="rounded-md bg-indigo-700 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-600">
                            تجربة حسابات الديمو
                        </a>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-950">حسابات الديمو</h2>
                    <div class="mt-5 space-y-3 text-sm">
                        @foreach ([
                            'super@example.com' => 'Super Admin',
                            'admin@example.com' => 'Company Admin',
                            'dispatcher@example.com' => 'Dispatcher',
                            'technician@example.com' => 'Technician',
                            'customer@example.com' => 'Customer',
                        ] as $email => $role)
                            <div class="flex items-center justify-between gap-4 rounded-md bg-gray-50 px-4 py-3">
                                <span class="font-medium text-gray-800">{{ $role }}</span>
                                <span class="font-mono text-xs text-gray-600">{{ $email }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-4 text-sm text-gray-500">كلمة المرور لكل الحسابات: <span class="font-mono">password</span></p>
                </div>
            </section>
        </main>
    </body>
</html>
