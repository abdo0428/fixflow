<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $asset->name }} - QR - {{ config('app.name', 'FixFlow') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gray-100 font-sans text-gray-900 antialiased">
        <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">بطاقة الجهاز</h1>
                    <p class="mt-1 text-sm text-gray-500">معلومات عامة آمنة من رمز QR</p>
                </div>
                <div class="flex gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            لوحة التحكم
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            تسجيل الدخول
                        </a>
                    @endauth
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="bg-white rounded-lg shadow-sm p-6 lg:col-span-2">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <div class="text-sm text-gray-500">اسم الجهاز</div>
                            <div class="mt-1 font-medium text-gray-900">{{ $asset->name }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">النوع</div>
                            <div class="mt-1 font-medium text-gray-900">{{ $asset->type }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">الماركة</div>
                            <div class="mt-1 font-medium text-gray-900">{{ $asset->brand ?: 'غير محدد' }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">الموديل</div>
                            <div class="mt-1 font-medium text-gray-900">{{ $asset->model ?: 'غير محدد' }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">الرقم التسلسلي</div>
                            <div class="mt-1 font-medium text-gray-900">{{ $asset->serial_number }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">حالة الضمان</div>
                            <div class="mt-1 font-medium text-gray-900">{{ $warrantyLabel }}</div>
                        </div>
                    </div>

                    @if (! $canViewSensitiveData)
                        <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                            تم إخفاء تفاصيل العميل وسجل الصيانة. سجل الدخول بحساب مصرح له لعرض المزيد.
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="font-semibold text-gray-900">QR Code</div>
                    <div class="mt-4 rounded-lg border border-gray-200 bg-white p-4">
                        {!! $qrSvg !!}
                    </div>
                    <div class="mt-3 break-all text-xs text-gray-500">{{ $asset->qr_code }}</div>
                </div>
            </div>

            @if ($canViewSensitiveData)
                <div class="mt-6 bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-100 font-semibold text-gray-900">آخر طلبات الصيانة</div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($recentRequests as $serviceRequest)
                            <div class="px-6 py-4">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <a href="{{ route('service-requests.show', $serviceRequest) }}" class="font-medium text-gray-900 hover:text-indigo-700">
                                        {{ $serviceRequest->title }}
                                    </a>
                                    <span class="text-sm text-gray-600">{{ $serviceRequest->status }}</span>
                                </div>
                                <div class="mt-1 text-sm text-gray-500">
                                    {{ optional($serviceRequest->created_at)->format('Y-m-d') }} - {{ $serviceRequest->customer?->name }}
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-10 text-center text-gray-500">لا توجد طلبات صيانة لهذا الجهاز بعد.</div>
                        @endforelse
                    </div>
                </div>
            @endif
        </main>
    </body>
</html>

