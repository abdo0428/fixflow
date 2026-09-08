<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" dir="rtl">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">فاتورة {{ $invoice->invoice_number }}</h2>
                <p class="mt-1 text-sm text-gray-500">مرتبطة بطلب #{{ $serviceRequest->id }}</p>
            </div>
            <a href="{{ route('service-requests.show', $serviceRequest) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                العودة إلى الطلب
            </a>
        </div>
    </x-slot>

    <div class="py-8" dir="rtl">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <div class="text-sm text-gray-500">حالة الفاتورة</div>
                        <div class="mt-1 font-semibold text-gray-900">{{ $invoice->status }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">تاريخ الإصدار</div>
                        <div class="mt-1 font-semibold text-gray-900">{{ optional($invoice->issued_at)->format('Y-m-d') ?: 'غير مصدر' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">تاريخ الدفع</div>
                        <div class="mt-1 font-semibold text-gray-900">{{ optional($invoice->paid_at)->format('Y-m-d') ?: 'غير مدفوعة' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">الإجمالي</div>
                        <div class="mt-1 font-semibold text-gray-900">{{ number_format((float) $invoice->total, 2) }}</div>
                    </div>
                </div>

                @can('update', $invoice)
                    @if ($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                        <form method="POST" action="{{ route('invoices.mark-paid', $invoice) }}" class="mt-6">
                            @csrf
                            <button type="submit" class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                                Mark as Paid
                            </button>
                        </form>
                    @endif
                @endcan
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="font-semibold text-gray-900">بيانات الفاتورة</div>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">الشركة</dt>
                            <dd class="font-medium text-gray-900">{{ $invoice->company?->name }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">العميل</dt>
                            <dd class="font-medium text-gray-900">{{ $serviceRequest->customer?->name }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">الجهاز</dt>
                            <dd class="font-medium text-gray-900">{{ $serviceRequest->serviceAsset?->name ?? 'طلب عام' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">طلب الصيانة</dt>
                            <dd class="font-medium text-gray-900">{{ $serviceRequest->title }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="font-semibold text-gray-900">ملخص الحساب</div>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">تكلفة الخدمة</dt>
                            <dd class="font-medium text-gray-900">{{ number_format((float) $invoice->service_cost, 2) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">تكلفة قطع الغيار</dt>
                            <dd class="font-medium text-gray-900">{{ number_format((float) $invoice->parts_total, 2) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">المجموع قبل الضريبة</dt>
                            <dd class="font-medium text-gray-900">{{ number_format((float) $invoice->subtotal, 2) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">الضريبة {{ number_format((float) $invoice->tax_rate * 100, 2) }}%</dt>
                            <dd class="font-medium text-gray-900">{{ number_format((float) $invoice->tax, 2) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-t border-gray-200 pt-3">
                            <dt class="font-semibold text-gray-900">الإجمالي</dt>
                            <dd class="font-semibold text-gray-900">{{ number_format((float) $invoice->total, 2) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 font-semibold text-gray-900">قطع الغيار</div>
                <div class="divide-y divide-gray-100">
                    @forelse ($serviceRequest->partsUsed as $partUsed)
                        <div class="px-6 py-4 flex items-center justify-between gap-4">
                            <div>
                                <div class="font-medium text-gray-900">{{ $partUsed->part?->name }}</div>
                                <div class="text-sm text-gray-500">{{ $partUsed->part?->sku }}</div>
                            </div>
                            <div class="text-sm text-gray-600">{{ $partUsed->quantity }} × {{ number_format((float) $partUsed->unit_price, 2) }}</div>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center text-gray-500">لا توجد قطع غيار مستخدمة.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
