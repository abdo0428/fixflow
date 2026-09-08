<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="'لوحة الشركة - '.$company->name" subtitle="ملخص تشغيلي سريع للشركة." dir="rtl" />
    </x-slot>

    <div class="ff-page" dir="rtl">
        <div class="ff-container space-y-6">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
                <x-ui.stat-card title="الفريق" :value="number_format($stats['users'])" />
                <x-ui.stat-card title="العملاء" :value="number_format($stats['customers'])" tone="info" />
                <x-ui.stat-card title="الأجهزة" :value="number_format($stats['assets'])" tone="success" />
                <x-ui.stat-card title="طلبات مفتوحة" :value="number_format($stats['openRequests'])" tone="warning" />
                <x-ui.stat-card title="قطع الغيار" :value="number_format($stats['parts'])" />
                <x-ui.stat-card title="الفواتير" :value="number_format($stats['invoices'])" tone="success" />
            </div>

            <x-ui.card title="أحدث طلبات الصيانة" padding="p-0">
                <div class="divide-y divide-slate-100">
                    @forelse ($recentRequests as $request)
                        <div class="px-5 py-4">
                            <div class="flex items-center justify-between gap-4">
                                <div class="font-medium text-slate-950">{{ $request->title }}</div>
                                <x-ui.badge :status="$request->status" />
                            </div>
                            <div class="mt-1 text-sm text-slate-500">
                                {{ $request->customer?->name }} - {{ $request->serviceAsset?->name ?? 'طلب عام' }}
                            </div>
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
</x-app-layout>
