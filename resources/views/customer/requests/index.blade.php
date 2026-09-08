<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                طلباتي
            </h2>
            <a href="{{ route('customer.service-requests.create') }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-900">طلب صيانة جديد</a>
        </div>
    </x-slot>

    <div class="py-8" dir="rtl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            @forelse ($requests as $serviceRequest)
                <div class="bg-white rounded-lg shadow-sm p-5 space-y-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $serviceRequest->title }}</h3>
                            <div class="mt-1 text-sm text-gray-500">{{ $serviceRequest->serviceAsset?->name ?? 'بدون جهاز' }} - {{ optional($serviceRequest->preferred_date)->format('Y-m-d') ?: 'بدون تاريخ مفضل' }}</div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700">{{ $statusLabels[$serviceRequest->status] ?? $serviceRequest->status }}</span>
                            <span class="rounded-full bg-amber-50 px-3 py-1 text-sm text-amber-800">{{ $priorityLabels[$serviceRequest->priority] ?? $serviceRequest->priority }}</span>
                        </div>
                    </div>

                    <p class="text-sm leading-6 text-gray-600">{{ $serviceRequest->description ?: 'لا يوجد وصف تفصيلي.' }}</p>

                    <div class="grid gap-6 lg:grid-cols-3">
                        <div>
                            <div class="font-medium text-gray-900">Timeline</div>
                            <div class="mt-3 space-y-3">
                                @forelse ($timelines->get($serviceRequest->id, collect()) as $event)
                                    <div class="border-r-2 border-indigo-200 pr-4">
                                        <div class="text-sm text-gray-900">{{ $event->action }}</div>
                                        <div class="text-xs text-gray-500">{{ optional($event->created_at)->format('Y-m-d H:i') }}</div>
                                    </div>
                                @empty
                                    <div class="text-sm text-gray-500">لا توجد أحداث مسجلة بعد.</div>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <div class="font-medium text-gray-900">التقرير النهائي</div>
                            @if ($serviceRequest->report)
                                <div class="mt-3 text-sm leading-6 text-gray-600">
                                    <div><span class="font-medium text-gray-900">التشخيص:</span> {{ $serviceRequest->report->diagnosis }}</div>
                                    <div><span class="font-medium text-gray-900">الحل:</span> {{ $serviceRequest->report->solution }}</div>
                                    <div><span class="font-medium text-gray-900">الفني:</span> {{ $serviceRequest->report->technician?->name }}</div>
                                    @if ($serviceRequest->report->pdf_path)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($serviceRequest->report->pdf_path) }}" target="_blank" class="mt-2 inline-flex font-medium text-indigo-700 hover:text-indigo-900">
                                            عرض PDF
                                        </a>
                                    @endif
                                </div>
                            @else
                                <div class="mt-3 text-sm text-gray-500">سيظهر التقرير هنا عند اكتمال الطلب.</div>
                            @endif
                        </div>

                        <div>
                            <div class="font-medium text-gray-900">الفاتورة</div>
                            @if ($serviceRequest->invoice)
                                <dl class="mt-3 space-y-2 text-sm">
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-gray-500">رقم الفاتورة</dt>
                                        <dd class="font-medium text-gray-900">{{ $serviceRequest->invoice->invoice_number }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-gray-500">الإجمالي</dt>
                                        <dd class="font-medium text-gray-900">{{ number_format((float) $serviceRequest->invoice->total, 2) }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-gray-500">الحالة</dt>
                                        <dd class="font-medium text-gray-900">{{ $serviceRequest->invoice->status }}</dd>
                                    </div>
                                </dl>
                                <a href="{{ route('invoices.show', $serviceRequest->invoice) }}" class="mt-3 inline-flex font-medium text-indigo-700 hover:text-indigo-900">
                                    عرض الفاتورة
                                </a>
                            @else
                                <div class="mt-3 text-sm text-gray-500">لا توجد فاتورة مرتبطة بهذا الطلب.</div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow-sm px-5 py-12 text-center text-gray-500">
                    لا توجد طلبات صيانة مرتبطة بحسابك بعد.
                </div>
            @endforelse

            {{ $requests->links() }}
        </div>
    </div>
</x-app-layout>
