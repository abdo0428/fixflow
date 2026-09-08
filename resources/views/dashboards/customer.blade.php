<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="بوابة العميل" subtitle="ملخص أجهزتك وطلبات الصيانة الأخيرة." dir="rtl" />
    </x-slot>

    <div class="ff-page" dir="rtl">
        <div class="ff-container space-y-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.stat-card title="أجهزتي" :value="number_format($assets->count())" />
                <x-ui.stat-card title="طلباتي" :value="number_format($requests->count())" tone="info" />
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <x-ui.card title="الأجهزة" padding="p-0">
                    <div class="divide-y divide-slate-100">
                        @forelse ($assets as $asset)
                            <div class="px-5 py-4">
                                <div class="font-medium text-slate-950">{{ $asset->name }}</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $asset->type }} - {{ $asset->serial_number }}</div>
                            </div>
                        @empty
                            <div class="p-5">
                                <x-ui.empty-state title="لا توجد أجهزة مرتبطة بحسابك." />
                            </div>
                        @endforelse
                    </div>
                </x-ui.card>

                <x-ui.card title="طلبات الصيانة" padding="p-0">
                    <div class="divide-y divide-slate-100">
                        @forelse ($requests as $request)
                            <div class="px-5 py-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="font-medium text-slate-950">{{ $request->title }}</div>
                                    <x-ui.badge :status="$request->status" />
                                </div>
                                <div class="mt-1 text-sm text-slate-500">{{ $request->serviceAsset?->name ?? 'طلب عام' }}</div>
                            </div>
                        @empty
                            <div class="p-5">
                                <x-ui.empty-state title="لا توجد طلبات مرتبطة بحسابك." />
                            </div>
                        @endforelse
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-app-layout>
