<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            لوحة الشركة - {{ $company->name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">الفريق</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['users']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">العملاء</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['customers']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">الأجهزة</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['assets']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">طلبات مفتوحة</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['openRequests']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">قطع الغيار</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['parts']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">الفواتير</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['invoices']) }}</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-900">أحدث طلبات الصيانة</div>
                <div class="divide-y divide-gray-100">
                    @forelse ($recentRequests as $request)
                        <div class="px-5 py-4">
                            <div class="flex items-center justify-between gap-4">
                                <div class="font-medium text-gray-900">{{ $request->title }}</div>
                                <span class="text-sm text-gray-600">{{ $request->status }}</span>
                            </div>
                            <div class="mt-1 text-sm text-gray-500">
                                {{ $request->customer?->name }} - {{ $request->serviceAsset?->name ?? 'طلب عام' }}
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-4 text-gray-500">لا توجد طلبات صيانة بعد.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
