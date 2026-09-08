<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            بوابة العميل
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">أجهزتي</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($assets->count()) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">طلباتي</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($requests->count()) }}</div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-900">الأجهزة</div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($assets as $asset)
                            <div class="px-5 py-4">
                                <div class="font-medium text-gray-900">{{ $asset->name }}</div>
                                <div class="mt-1 text-sm text-gray-500">{{ $asset->type }} - {{ $asset->serial_number }}</div>
                            </div>
                        @empty
                            <div class="px-5 py-4 text-gray-500">لا توجد أجهزة مرتبطة بحسابك.</div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-900">طلبات الصيانة</div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($requests as $request)
                            <div class="px-5 py-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="font-medium text-gray-900">{{ $request->title }}</div>
                                    <span class="text-sm text-gray-600">{{ $request->status }}</span>
                                </div>
                                <div class="mt-1 text-sm text-gray-500">{{ $request->serviceAsset?->name ?? 'طلب عام' }}</div>
                            </div>
                        @empty
                            <div class="px-5 py-4 text-gray-500">لا توجد طلبات مرتبطة بحسابك.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
