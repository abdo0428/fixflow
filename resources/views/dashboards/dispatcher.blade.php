<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            طلبات الصيانة
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">جديدة</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['newRequests']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">مجدولة</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['scheduledRequests']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">قيد التنفيذ</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['inProgressRequests']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">زيارات اليوم</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['visitsToday']) }}</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-900">قائمة الطلبات</div>
                <div class="divide-y divide-gray-100">
                    @forelse ($requests as $request)
                        <div class="px-5 py-4">
                            <div class="flex items-center justify-between gap-4">
                                <div class="font-medium text-gray-900">{{ $request->title }}</div>
                                <span class="text-sm text-gray-600">{{ $request->priority }} / {{ $request->status }}</span>
                            </div>
                            <div class="mt-1 text-sm text-gray-500">
                                {{ $request->customer?->name }} - {{ $request->serviceAsset?->serial_number ?? 'بدون جهاز' }}
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-4 text-gray-500">لا توجد طلبات ضمن شركتك.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
