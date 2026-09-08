<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between" dir="rtl">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $title }} - {{ $company->name }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
            </div>
            <a href="{{ route('service-requests.index') }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                عرض الطلبات
            </a>
        </div>
    </x-slot>

    <div class="py-8" dir="rtl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">طلبات اليوم</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['requestsToday']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">الطلبات المفتوحة</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['openRequests']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">مكتملة هذا الشهر</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['completedThisMonth']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">طلبات متأخرة</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['overdueRequests']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">فنيون نشطون</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['activeTechnicians']) }}</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-5">
                <div class="font-semibold text-gray-900">روابط سريعة</div>
                <div class="mt-4 flex flex-wrap gap-3">
                    @foreach ($quickLinks as $link)
                        @if ($link['enabled'])
                            <a href="{{ $link['url'] }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                {{ $link['label'] }}
                            </a>
                        @else
                            <span class="inline-flex items-center rounded-md border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-400">
                                {{ $link['label'] }}
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="font-semibold text-gray-900">عدد الطلبات حسب الحالة</div>
                    <div class="mt-5 space-y-3">
                        @php($statusMax = max(1, $requestsByStatus->max()))
                        @foreach ($requestsByStatus as $status => $count)
                            <div>
                                <div class="mb-1 flex items-center justify-between text-sm">
                                    <span class="font-medium text-gray-700">{{ $status }}</span>
                                    <span class="text-gray-500">{{ number_format($count) }}</span>
                                </div>
                                <div class="h-2 rounded-full bg-gray-100">
                                    <div class="h-2 rounded-full bg-indigo-600" style="width: {{ ($count / $statusMax) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="font-semibold text-gray-900">عدد الطلبات حسب الأولوية</div>
                    <div class="mt-5 space-y-3">
                        @php($priorityMax = max(1, $requestsByPriority->max()))
                        @foreach ($requestsByPriority as $priority => $count)
                            <div>
                                <div class="mb-1 flex items-center justify-between text-sm">
                                    <span class="font-medium text-gray-700">{{ $priority }}</span>
                                    <span class="text-gray-500">{{ number_format($count) }}</span>
                                </div>
                                <div class="h-2 rounded-full bg-gray-100">
                                    <div class="h-2 rounded-full bg-emerald-600" style="width: {{ ($count / $priorityMax) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-3">
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-900">أكثر أنواع الأجهزة طلبًا</div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($topAssetTypes as $assetType)
                            <div class="px-5 py-4 flex items-center justify-between gap-4">
                                <div class="font-medium text-gray-900">{{ $assetType->type }}</div>
                                <div class="text-sm text-gray-600">{{ number_format($assetType->total) }} طلب</div>
                            </div>
                        @empty
                            <div class="px-5 py-10 text-center text-gray-500">لا توجد طلبات مرتبطة بأجهزة بعد.</div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-900">أكثر الفنيين إنجازًا</div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($topTechnicians as $technician)
                            <div class="px-5 py-4 flex items-center justify-between gap-4">
                                <div class="font-medium text-gray-900">{{ $technician->name }}</div>
                                <div class="text-sm text-gray-600">{{ number_format($technician->completed_requests) }} طلب مكتمل</div>
                            </div>
                        @empty
                            <div class="px-5 py-10 text-center text-gray-500">لا توجد طلبات مكتملة مرتبطة بفنيين بعد.</div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-900">قطع منخفضة المخزون</div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($lowStockParts as $part)
                            <div class="px-5 py-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="font-medium text-gray-900">{{ $part->name }}</div>
                                    <div class="text-sm text-red-700">{{ number_format($part->quantity) }} متبقي</div>
                                </div>
                                <div class="mt-1 text-sm text-gray-500">{{ $part->sku }} - حد التنبيه {{ number_format($part->low_stock_threshold) }}</div>
                            </div>
                        @empty
                            <div class="px-5 py-10 text-center text-gray-500">لا توجد قطع منخفضة المخزون حاليًا.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-900">أحدث طلبات الصيانة</div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($recentRequests as $serviceRequest)
                            <div class="px-5 py-4">
                                <div class="flex items-center justify-between gap-4">
                                    <a href="{{ route('service-requests.show', $serviceRequest) }}" class="font-medium text-gray-900 hover:text-indigo-700">
                                        {{ $serviceRequest->title }}
                                    </a>
                                    <span class="text-sm text-gray-600">{{ $serviceRequest->status }}</span>
                                </div>
                                <div class="mt-1 text-sm text-gray-500">
                                    {{ $serviceRequest->customer?->name }} - {{ $serviceRequest->serviceAsset?->name ?? 'طلب عام' }}
                                </div>
                            </div>
                        @empty
                            <div class="px-5 py-10 text-center text-gray-500">لا توجد طلبات صيانة بعد.</div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-900">طلبات تحتاج متابعة</div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($overdueRequests as $serviceRequest)
                            <div class="px-5 py-4">
                                <div class="flex items-center justify-between gap-4">
                                    <a href="{{ route('service-requests.show', $serviceRequest) }}" class="font-medium text-gray-900 hover:text-indigo-700">
                                        {{ $serviceRequest->title }}
                                    </a>
                                    <span class="text-sm text-red-700">{{ optional($serviceRequest->preferred_date)->format('Y-m-d') }}</span>
                                </div>
                                <div class="mt-1 text-sm text-gray-500">
                                    {{ $serviceRequest->customer?->name }} - {{ $serviceRequest->status }}
                                </div>
                            </div>
                        @empty
                            <div class="px-5 py-10 text-center text-gray-500">لا توجد طلبات متأخرة حاليًا.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="text-xs text-gray-400">
                يتم تخزين هذه المؤشرات مؤقتًا لمدة {{ $cacheMinutes }} دقائق.
            </div>
        </div>
    </div>
</x-app-layout>
