<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="طلباتي" subtitle="متابعة حالة طلبات الصيانة والتقارير والفواتير." dir="rtl">
            <x-slot name="actions">
                <x-ui.button :href="route('customer.service-requests.create')">طلب صيانة جديد</x-ui.button>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="ff-page" dir="rtl">
        <div class="ff-container space-y-6">
            @if (session('status'))
                <div class="ff-alert-success">{{ session('status') }}</div>
            @endif

            @forelse ($requests as $serviceRequest)
                <x-ui.card>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-950">{{ $serviceRequest->title }}</h3>
                            <div class="mt-1 text-sm text-slate-500">{{ $serviceRequest->serviceAsset?->name ?? 'بدون جهاز' }} - {{ optional($serviceRequest->preferred_date)->format('Y-m-d') ?: 'بدون تاريخ مفضل' }}</div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-ui.badge :status="$serviceRequest->status">{{ $statusLabels[$serviceRequest->status] ?? $serviceRequest->status }}</x-ui.badge>
                            <x-ui.badge :status="$serviceRequest->priority">{{ $priorityLabels[$serviceRequest->priority] ?? $serviceRequest->priority }}</x-ui.badge>
                        </div>
                    </div>

                    <p class="mt-5 text-sm leading-6 text-slate-600">{{ $serviceRequest->description ?: 'لا يوجد وصف تفصيلي.' }}</p>

                    <div class="mt-6 grid gap-6 lg:grid-cols-3">
                        <div>
                            <div class="ff-section-title">Timeline</div>
                            <div class="mt-3">
                                <x-ui.status-timeline :items="$timelines->get($serviceRequest->id, collect())" />
                            </div>
                        </div>

                        <div>
                            <div class="ff-section-title">التقرير النهائي</div>
                            @if ($serviceRequest->report)
                                <div class="mt-3 text-sm leading-6 text-slate-600">
                                    <div><span class="font-medium text-slate-900">التشخيص:</span> {{ $serviceRequest->report->diagnosis }}</div>
                                    <div><span class="font-medium text-slate-900">الحل:</span> {{ $serviceRequest->report->solution }}</div>
                                    <div><span class="font-medium text-slate-900">الفني:</span> {{ $serviceRequest->report->technician?->name }}</div>
                                    @if ($serviceRequest->report->pdf_path)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($serviceRequest->report->pdf_path) }}" target="_blank" class="mt-2 inline-flex font-medium text-cyan-700 hover:text-cyan-900">
                                            عرض PDF
                                        </a>
                                    @endif
                                </div>
                            @else
                                <div class="mt-3 text-sm text-slate-500">سيظهر التقرير هنا عند اكتمال الطلب.</div>
                            @endif
                        </div>

                        <div>
                            <div class="ff-section-title">الفاتورة</div>
                            @if ($serviceRequest->invoice)
                                <dl class="mt-3 space-y-2 text-sm">
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-slate-500">رقم الفاتورة</dt>
                                        <dd class="font-medium text-slate-900">{{ $serviceRequest->invoice->invoice_number }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-slate-500">الإجمالي</dt>
                                        <dd class="font-medium text-slate-900">{{ number_format((float) $serviceRequest->invoice->total, 2) }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-slate-500">الحالة</dt>
                                        <dd><x-ui.badge :status="$serviceRequest->invoice->status" /></dd>
                                    </div>
                                </dl>
                                <x-ui.button :href="route('invoices.show', $serviceRequest->invoice)" variant="secondary" size="sm" class="mt-4">عرض الفاتورة</x-ui.button>
                            @else
                                <div class="mt-3 text-sm text-slate-500">لا توجد فاتورة مرتبطة بهذا الطلب.</div>
                            @endif
                        </div>
                    </div>
                </x-ui.card>
            @empty
                <x-ui.empty-state
                    title="لا توجد طلبات صيانة مرتبطة بحسابك بعد."
                    message="يمكنك إرسال طلب جديد من بوابة العميل وسيظهر هنا فورًا."
                    :href="route('customer.service-requests.create')"
                    action="طلب صيانة جديد"
                />
            @endforelse

            {{ $requests->links() }}
        </div>
    </div>
</x-app-layout>
