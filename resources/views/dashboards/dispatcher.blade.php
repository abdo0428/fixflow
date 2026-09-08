<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="طلبات الصيانة" subtitle="ملخص سريع لفريق الجدولة والتوزيع." dir="rtl" />
    </x-slot>

    <div class="ff-page" dir="rtl">
        <div class="ff-container space-y-6">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-ui.stat-card title="جديدة" :value="number_format($stats['newRequests'])" />
                <x-ui.stat-card title="مجدولة" :value="number_format($stats['scheduledRequests'])" tone="info" />
                <x-ui.stat-card title="قيد التنفيذ" :value="number_format($stats['inProgressRequests'])" tone="warning" />
                <x-ui.stat-card title="زيارات اليوم" :value="number_format($stats['visitsToday'])" tone="success" />
            </div>

            <x-ui.card title="قائمة الطلبات" padding="p-0">
                <div class="divide-y divide-slate-100">
                    @forelse ($requests as $request)
                        <div class="px-5 py-4">
                            <div class="flex items-center justify-between gap-4">
                                <div class="font-medium text-slate-950">{{ $request->title }}</div>
                                <div class="flex gap-2">
                                    <x-ui.badge :status="$request->priority" />
                                    <x-ui.badge :status="$request->status" />
                                </div>
                            </div>
                            <div class="mt-1 text-sm text-slate-500">
                                {{ $request->customer?->name }} - {{ $request->serviceAsset?->serial_number ?? 'بدون جهاز' }}
                            </div>
                        </div>
                    @empty
                        <div class="p-5">
                            <x-ui.empty-state title="لا توجد طلبات ضمن شركتك." />
                        </div>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
