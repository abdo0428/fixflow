<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="الفواتير" subtitle="آخر الفواتير المرتبطة بطلبات الصيانة." dir="rtl" />
    </x-slot>

    <div class="ff-page" dir="rtl">
        <div class="ff-container space-y-6">
            <x-ui.table
                title="آخر الفواتير"
                :columns="['رقم الفاتورة', 'العميل', 'طلب الصيانة', 'الإجمالي', 'الحالة', '']"
                :footer="$invoices->links()"
            >
                @forelse ($invoices as $invoice)
                    <tr>
                        <td class="font-medium text-slate-950">{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->serviceRequest?->customer?->name }}</td>
                        <td>{{ $invoice->serviceRequest?->title }}</td>
                        <td>{{ number_format((float) $invoice->total, 2) }}</td>
                        <td><x-ui.badge :status="$invoice->status" /></td>
                        <td class="text-end">
                            <a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-cyan-700 hover:text-cyan-900">عرض</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-ui.empty-state title="لا توجد فواتير بعد." />
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>
    </div>
</x-app-layout>
