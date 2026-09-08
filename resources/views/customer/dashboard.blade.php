<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="بوابة العميل" subtitle="أجهزتك وطلبات الصيانة الخاصة بك في مكان واحد." dir="rtl">
            <x-slot name="actions">
                <x-ui.button :href="route('customer.service-requests.create')">طلب صيانة جديد</x-ui.button>
                <x-ui.button :href="route('customer.assets.index')" variant="secondary">أجهزتي</x-ui.button>
                <x-ui.button :href="route('customer.service-requests.index')" variant="secondary">طلباتي</x-ui.button>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="ff-page" dir="rtl">
        <div class="ff-container space-y-6">
            @if (session('status'))
                <div class="ff-alert-success">{{ session('status') }}</div>
            @endif

            <div class="grid gap-4 sm:grid-cols-3">
                <x-ui.stat-card title="أجهزتي" :value="number_format($stats['assets'])" />
                <x-ui.stat-card title="طلباتي المفتوحة" :value="number_format($stats['openRequests'])" tone="warning" />
                <x-ui.stat-card title="كل الطلبات" :value="number_format($stats['allRequests'])" tone="info" />
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <x-ui.card title="أجهزتي" padding="p-0">
                    <div class="divide-y divide-slate-100">
                        @forelse ($assets as $summary)
                            <div class="px-5 py-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="font-medium text-slate-950">{{ $summary['asset']->name }}</div>
                                    <x-ui.badge variant="info">{{ $summary['warranty_label'] }}</x-ui.badge>
                                </div>
                                <div class="mt-1 text-sm text-slate-500">{{ $summary['asset']->type }} - {{ $summary['asset']->serial_number }}</div>
                            </div>
                        @empty
                            <div class="p-5">
                                <x-ui.empty-state title="لا توجد أجهزة مرتبطة بحسابك بعد." />
                            </div>
                        @endforelse
                    </div>
                </x-ui.card>

                <x-ui.card title="آخر الحالات" padding="p-0">
                    <div class="divide-y divide-slate-100">
                        @forelse ($latestRequests as $serviceRequest)
                            <div class="px-5 py-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="font-medium text-slate-950">{{ $serviceRequest->title }}</div>
                                    <x-ui.badge :status="$serviceRequest->status">{{ $statusLabels[$serviceRequest->status] ?? $serviceRequest->status }}</x-ui.badge>
                                </div>
                                <div class="mt-1 text-sm text-slate-500">{{ $serviceRequest->serviceAsset?->name ?? 'بدون جهاز' }}</div>
                            </div>
                        @empty
                            <div class="p-5">
                                <x-ui.empty-state title="لا توجد طلبات صيانة بعد." />
                            </div>
                        @endforelse
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-app-layout>
