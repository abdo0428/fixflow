<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            لوحة الفني
        </h2>
    </x-slot>

    <div class="py-8" dir="rtl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">زيارات اليوم</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['todayVisits']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">الزيارات القادمة</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['upcomingVisits']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">طلبات قيد التنفيذ</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['inProgressRequests']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">بانتظار قطع غيار</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['waitingPartsRequests']) }}</div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-gray-100">
                        <div class="font-semibold text-gray-900">زيارات اليوم</div>
                        <a href="{{ route('technician.visits.index', ['date' => today()->toDateString()]) }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-900">عرض الكل</a>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($todayVisits as $visit)
                            <a href="{{ route('technician.visits.show', $visit) }}" class="block px-5 py-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="font-medium text-gray-900">{{ $visit->serviceRequest?->title }}</div>
                                    <span class="text-sm text-gray-600">{{ $visitLabels[$visit->visit_status] ?? $visit->visit_status }}</span>
                                </div>
                                <div class="mt-1 text-sm text-gray-500">
                                    {{ optional($visit->scheduled_at)->format('Y-m-d H:i') }} - {{ $visit->serviceRequest?->customer?->name }}
                                </div>
                            </a>
                        @empty
                            <div class="px-5 py-10 text-center text-gray-500">لا توجد زيارات مجدولة لهذا اليوم.</div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm">
                    <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-gray-100">
                        <div class="font-semibold text-gray-900">الزيارات القادمة</div>
                        <a href="{{ route('technician.visits.index') }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-900">كل الزيارات</a>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($upcomingVisits as $visit)
                            <a href="{{ route('technician.visits.show', $visit) }}" class="block px-5 py-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="font-medium text-gray-900">{{ $visit->serviceRequest?->title }}</div>
                                    <span class="text-sm text-gray-600">{{ $visitLabels[$visit->visit_status] ?? $visit->visit_status }}</span>
                                </div>
                                <div class="mt-1 text-sm text-gray-500">
                                    {{ optional($visit->scheduled_at)->format('Y-m-d H:i') }} - {{ $visit->location_address }}
                                </div>
                            </a>
                        @empty
                            <div class="px-5 py-10 text-center text-gray-500">لا توجد زيارات قادمة حاليًا.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

