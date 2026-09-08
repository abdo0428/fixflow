<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight" dir="rtl">
            الفواتير
        </h2>
    </x-slot>

    <div class="py-8" dir="rtl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-900">آخر الفواتير</div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-5 py-3 text-right font-medium">رقم الفاتورة</th>
                                <th class="px-5 py-3 text-right font-medium">العميل</th>
                                <th class="px-5 py-3 text-right font-medium">طلب الصيانة</th>
                                <th class="px-5 py-3 text-right font-medium">الإجمالي</th>
                                <th class="px-5 py-3 text-right font-medium">الحالة</th>
                                <th class="px-5 py-3 text-right font-medium"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($invoices as $invoice)
                                <tr>
                                    <td class="px-5 py-4 font-medium text-gray-900">{{ $invoice->invoice_number }}</td>
                                    <td class="px-5 py-4 text-gray-600">{{ $invoice->serviceRequest?->customer?->name }}</td>
                                    <td class="px-5 py-4 text-gray-600">{{ $invoice->serviceRequest?->title }}</td>
                                    <td class="px-5 py-4 text-gray-600">{{ number_format((float) $invoice->total, 2) }}</td>
                                    <td class="px-5 py-4 text-gray-600">{{ $invoice->status }}</td>
                                    <td class="px-5 py-4">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-indigo-700 hover:text-indigo-900">عرض</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-gray-500">لا توجد فواتير بعد.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-4">
                    {{ $invoices->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
