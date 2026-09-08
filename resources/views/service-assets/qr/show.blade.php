<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $asset->name }} - QR - {{ config('app.name', 'FixFlow') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="ff-shell font-sans antialiased">
        <main class="ff-container py-8">
            <x-ui.page-header title="بطاقة الجهاز" subtitle="معلومات عامة آمنة من رمز QR">
                <x-slot name="actions">
                    @auth
                        <x-ui.button :href="route('dashboard')" variant="secondary">لوحة التحكم</x-ui.button>
                    @else
                        <x-ui.button :href="route('login')">تسجيل الدخول</x-ui.button>
                    @endauth
                </x-slot>
            </x-ui.page-header>

            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                <x-ui.card :title="$asset->name" subtitle="بيانات الجهاز العامة" class="lg:col-span-2">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <div class="text-sm text-slate-500">اسم الجهاز</div>
                            <div class="mt-1 font-medium text-slate-950">{{ $asset->name }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-slate-500">النوع</div>
                            <div class="mt-1 font-medium text-slate-950">{{ $asset->type }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-slate-500">الماركة</div>
                            <div class="mt-1 font-medium text-slate-950">{{ $asset->brand ?: 'غير محدد' }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-slate-500">الموديل</div>
                            <div class="mt-1 font-medium text-slate-950">{{ $asset->model ?: 'غير محدد' }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-slate-500">الرقم التسلسلي</div>
                            <div class="mt-1 font-medium text-slate-950">{{ $asset->serial_number }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-slate-500">حالة الضمان</div>
                            <div class="mt-1"><x-ui.badge variant="info">{{ $warrantyLabel }}</x-ui.badge></div>
                        </div>
                    </div>

                    @if (! $canViewSensitiveData)
                        <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                            تم إخفاء تفاصيل العميل وسجل الصيانة. سجل الدخول بحساب مصرح له لعرض المزيد.
                        </div>
                    @endif
                </x-ui.card>

                <x-ui.card title="QR Code">
                    <div class="mt-4 rounded-lg border border-gray-200 bg-white p-4">
                        {!! $qrSvg !!}
                    </div>
                    <div class="mt-3 break-all text-xs text-slate-500">{{ $asset->qr_code }}</div>
                </x-ui.card>
            </div>

            @if ($canViewSensitiveData)
                <x-ui.card title="آخر طلبات الصيانة" class="mt-6" padding="p-0">
                    <div class="divide-y divide-gray-100">
                        @forelse ($recentRequests as $serviceRequest)
                            <div class="px-6 py-4">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <a href="{{ route('service-requests.show', $serviceRequest) }}" class="font-medium text-slate-950 hover:text-cyan-700">
                                        {{ $serviceRequest->title }}
                                    </a>
                                    <x-ui.badge :status="$serviceRequest->status" />
                                </div>
                                <div class="mt-1 text-sm text-slate-500">
                                    {{ optional($serviceRequest->created_at)->format('Y-m-d') }} - {{ $serviceRequest->customer?->name }}
                                </div>
                            </div>
                        @empty
                            <div class="p-6">
                                <x-ui.empty-state title="لا توجد طلبات صيانة لهذا الجهاز بعد." />
                            </div>
                        @endforelse
                    </div>
                </x-ui.card>
            @endif
        </main>
    </body>
</html>
