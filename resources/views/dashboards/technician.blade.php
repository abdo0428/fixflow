<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="زياراتي" subtitle="ملخص الزيارات المسندة وحالات العمل." dir="rtl" />
    </x-slot>

    <div class="ff-page" dir="rtl">
        <div class="ff-container space-y-6">
            <div class="grid gap-4 sm:grid-cols-3">
                <x-ui.stat-card title="مجدولة" :value="number_format($stats['scheduled'])" tone="info" />
                <x-ui.stat-card title="قيد التنفيذ" :value="number_format($stats['inProgress'])" tone="warning" />
                <x-ui.stat-card title="مكتملة" :value="number_format($stats['completed'])" tone="success" />
            </div>

            <x-ui.card title="الزيارات المسندة" padding="p-0">
                <div class="divide-y divide-slate-100">
                    @forelse ($visits as $visit)
                        <div class="px-5 py-4">
                            <div class="flex items-center justify-between gap-4">
                                <div class="font-medium text-slate-950">{{ $visit->serviceRequest?->title }}</div>
                                <x-ui.badge :status="$visit->visit_status" />
                            </div>
                            <div class="mt-1 text-sm text-slate-500">
                                {{ optional($visit->scheduled_at)->format('Y-m-d H:i') }} - {{ $visit->serviceRequest?->customer?->name }}
                            </div>
                        </div>
                    @empty
                        <div class="p-5">
                            <x-ui.empty-state title="لا توجد زيارات مسندة لك." />
                        </div>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
