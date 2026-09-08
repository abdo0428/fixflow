<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            زياراتي
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">مجدولة</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['scheduled']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">قيد التنفيذ</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['inProgress']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">مكتملة</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['completed']) }}</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-900">الزيارات المسندة</div>
                <div class="divide-y divide-gray-100">
                    @forelse ($visits as $visit)
                        <div class="px-5 py-4">
                            <div class="flex items-center justify-between gap-4">
                                <div class="font-medium text-gray-900">{{ $visit->serviceRequest?->title }}</div>
                                <span class="text-sm text-gray-600">{{ $visit->visit_status }}</span>
                            </div>
                            <div class="mt-1 text-sm text-gray-500">
                                {{ optional($visit->scheduled_at)->format('Y-m-d H:i') }} - {{ $visit->serviceRequest?->customer?->name }}
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-4 text-gray-500">لا توجد زيارات مسندة لك.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
