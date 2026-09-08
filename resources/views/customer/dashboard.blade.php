<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            بوابة العميل
        </h2>
    </x-slot>

    <div class="py-8" dir="rtl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">أجهزتي</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['assets']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">طلباتي المفتوحة</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['openRequests']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">كل الطلبات</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['allRequests']) }}</div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('customer.service-requests.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">طلب صيانة جديد</a>
                <a href="{{ route('customer.assets.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">عرض أجهزتي</a>
                <a href="{{ route('customer.service-requests.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">عرض طلباتي</a>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-900">أجهزتي</div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($assets as $summary)
                            <div class="px-5 py-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="font-medium text-gray-900">{{ $summary['asset']->name }}</div>
                                    <span class="text-sm text-gray-600">{{ $summary['warranty_label'] }}</span>
                                </div>
                                <div class="mt-1 text-sm text-gray-500">{{ $summary['asset']->type }} - {{ $summary['asset']->serial_number }}</div>
                            </div>
                        @empty
                            <div class="px-5 py-10 text-center text-gray-500">لا توجد أجهزة مرتبطة بحسابك بعد.</div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-900">آخر الحالات</div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($latestRequests as $serviceRequest)
                            <div class="px-5 py-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="font-medium text-gray-900">{{ $serviceRequest->title }}</div>
                                    <span class="text-sm text-gray-600">{{ $statusLabels[$serviceRequest->status] ?? $serviceRequest->status }}</span>
                                </div>
                                <div class="mt-1 text-sm text-gray-500">{{ $serviceRequest->serviceAsset?->name ?? 'بدون جهاز' }}</div>
                            </div>
                        @empty
                            <div class="px-5 py-10 text-center text-gray-500">لا توجد طلبات صيانة بعد.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

