<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="'فاتورة '.$invoice->invoice_number" :subtitle="'مرتبطة بطلب #'.$serviceRequest->id" dir="rtl">
            <x-slot name="actions">
                <x-ui.badge :status="$invoice->status" />
                <x-ui.button :href="route('service-requests.show', $serviceRequest)" variant="secondary" size="sm">العودة إلى الطلب</x-ui.button>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="ff-page" dir="rtl">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="ff-alert-success">{{ session('status') }}</div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-ui.stat-card title="حالة الفاتورة" :value="$invoice->status" />
                <x-ui.stat-card title="تاريخ الإصدار" :value="optional($invoice->issued_at)->format('Y-m-d') ?: 'غير مصدر'" tone="info" />
                <x-ui.stat-card title="تاريخ الدفع" :value="optional($invoice->paid_at)->format('Y-m-d') ?: 'غير مدفوعة'" tone="warning" />
                <x-ui.stat-card title="الإجمالي" :value="number_format((float) $invoice->total, 2)" tone="success" />
            </div>

            @can('update', $invoice)
                @if ($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                    <x-ui.card>
                        <form method="POST" action="{{ route('invoices.mark-paid', $invoice) }}">
                            @csrf
                            <x-ui.button type="submit" variant="success">Mark as Paid</x-ui.button>
                        </form>
                    </x-ui.card>
                @endif
            @endcan

            <div class="grid gap-6 lg:grid-cols-2">
                <x-ui.card title="بيانات الفاتورة">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">الشركة</dt>
                            <dd class="font-medium text-slate-950">{{ $invoice->company?->name }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">العميل</dt>
                            <dd class="font-medium text-slate-950">{{ $serviceRequest->customer?->name }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">الجهاز</dt>
                            <dd class="font-medium text-slate-950">{{ $serviceRequest->serviceAsset?->name ?? 'طلب عام' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">طلب الصيانة</dt>
                            <dd class="font-medium text-slate-950">{{ $serviceRequest->title }}</dd>
                        </div>
                    </dl>
                </x-ui.card>

                <x-ui.card title="ملخص الحساب">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">تكلفة الخدمة</dt>
                            <dd class="font-medium text-slate-950">{{ number_format((float) $invoice->service_cost, 2) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">تكلفة قطع الغيار</dt>
                            <dd class="font-medium text-slate-950">{{ number_format((float) $invoice->parts_total, 2) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">المجموع قبل الضريبة</dt>
                            <dd class="font-medium text-slate-950">{{ number_format((float) $invoice->subtotal, 2) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">الضريبة {{ number_format((float) $invoice->tax_rate * 100, 2) }}%</dt>
                            <dd class="font-medium text-slate-950">{{ number_format((float) $invoice->tax, 2) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-t border-slate-200 pt-3">
                            <dt class="font-semibold text-slate-950">الإجمالي</dt>
                            <dd class="font-semibold text-slate-950">{{ number_format((float) $invoice->total, 2) }}</dd>
                        </div>
                    </dl>
                </x-ui.card>
            </div>

            <x-ui.table title="قطع الغيار" :columns="['القطعة', 'SKU', 'الكمية والتكلفة']">
                @forelse ($serviceRequest->partsUsed as $partUsed)
                    <tr>
                        <td class="font-medium text-slate-950">{{ $partUsed->part?->name }}</td>
                        <td>{{ $partUsed->part?->sku }}</td>
                        <td>{{ $partUsed->quantity }} × {{ number_format((float) $partUsed->unit_price, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">
                            <x-ui.empty-state title="لا توجد قطع غيار مستخدمة." />
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>
    </div>
</x-app-layout>
