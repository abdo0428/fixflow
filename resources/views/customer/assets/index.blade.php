<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="أجهزتي" subtitle="الأجهزة والأصول المرتبطة بحسابك وحالة الضمان لكل جهاز." dir="rtl" />
    </x-slot>

    <div class="ff-page" dir="rtl">
        <div class="ff-container">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($assets as $summary)
                    <x-ui.card>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-semibold text-slate-950">{{ $summary['asset']->name }}</h3>
                                <div class="mt-1 text-sm text-slate-500">{{ $summary['asset']->type }} - {{ $summary['asset']->brand }} {{ $summary['asset']->model }}</div>
                            </div>
                            <x-ui.badge :status="$summary['asset']->status" />
                        </div>

                        <dl class="mt-4 space-y-2 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500">الرقم التسلسلي</dt>
                                <dd class="font-medium text-slate-900">{{ $summary['asset']->serial_number }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500">الضمان</dt>
                                <dd><x-ui.badge variant="info">{{ $summary['warranty_label'] }}</x-ui.badge></dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500">تاريخ الشراء</dt>
                                <dd class="font-medium text-slate-900">{{ optional($summary['asset']->purchase_date)->format('Y-m-d') ?: 'غير موثق' }}</dd>
                            </div>
                        </dl>
                    </x-ui.card>
                @empty
                    <div class="md:col-span-2 xl:col-span-3">
                        <x-ui.empty-state title="لا توجد أجهزة مرتبطة بحسابك بعد." message="ستظهر الأجهزة هنا بعد إضافتها من شركة الصيانة." />
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
