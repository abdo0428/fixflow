<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            أجهزتي
        </h2>
    </x-slot>

    <div class="py-8" dir="rtl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($assets as $summary)
                    <div class="bg-white rounded-lg shadow-sm p-5 space-y-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $summary['asset']->name }}</h3>
                                <div class="mt-1 text-sm text-gray-500">{{ $summary['asset']->type }} - {{ $summary['asset']->brand }} {{ $summary['asset']->model }}</div>
                            </div>
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700">{{ $summary['asset']->status }}</span>
                        </div>

                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">الرقم التسلسلي</dt>
                                <dd class="font-medium text-gray-900">{{ $summary['asset']->serial_number }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">الضمان</dt>
                                <dd class="font-medium text-gray-900">{{ $summary['warranty_label'] }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">تاريخ الشراء</dt>
                                <dd class="font-medium text-gray-900">{{ optional($summary['asset']->purchase_date)->format('Y-m-d') ?: 'غير موثق' }}</dd>
                            </div>
                        </dl>
                    </div>
                @empty
                    <div class="md:col-span-2 xl:col-span-3 bg-white rounded-lg shadow-sm px-5 py-12 text-center text-gray-500">
                        لا توجد أجهزة مرتبطة بحسابك بعد.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>

