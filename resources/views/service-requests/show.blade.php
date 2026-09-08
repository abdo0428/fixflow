<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" dir="rtl">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $serviceRequest->title }}</h2>
                <p class="mt-1 text-sm text-gray-500">طلب #{{ $serviceRequest->id }} - {{ $serviceRequest->status }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @can('update', $serviceRequest)
                    <a href="{{ route('service-requests.edit', $serviceRequest) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                        تعديل
                    </a>
                @endcan
                <a href="{{ route('service-requests.index') }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold text-white hover:bg-gray-700">
                    القائمة
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8" dir="rtl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <div class="text-sm text-gray-500">العميل</div>
                                <div class="mt-1 font-medium text-gray-900">{{ $serviceRequest->customer?->name }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">الجهاز</div>
                                <div class="mt-1 font-medium text-gray-900">{{ $serviceRequest->serviceAsset?->name ?? 'طلب عام' }}</div>
                                @if ($assetQrUrl)
                                    <a href="{{ $assetQrUrl }}" class="mt-1 inline-flex text-sm font-medium text-indigo-700 hover:text-indigo-900">
                                        فتح صفحة QR للجهاز
                                    </a>
                                @endif
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">الأولوية</div>
                                <div class="mt-1 font-medium text-gray-900">{{ $serviceRequest->priority }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">التاريخ المفضل</div>
                                <div class="mt-1 font-medium text-gray-900">{{ optional($serviceRequest->preferred_date)->format('Y-m-d') ?? 'غير محدد' }}</div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="text-sm text-gray-500">الوصف</div>
                            <p class="mt-2 text-gray-800 whitespace-pre-line">{{ $serviceRequest->description ?: 'لا يوجد وصف.' }}</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-100 font-semibold text-gray-900">الزيارات</div>
                        <div class="divide-y divide-gray-100">
                            @forelse ($serviceRequest->visits as $visit)
                                <div class="px-6 py-4">
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="font-medium text-gray-900">{{ $visit->technician?->name }}</div>
                                        <span class="text-sm text-gray-600">{{ $visit->visit_status }}</span>
                                    </div>
                                    <div class="mt-1 text-sm text-gray-500">
                                        {{ optional($visit->scheduled_at)->format('Y-m-d H:i') }} - {{ $visit->location_address }}
                                    </div>
                                    @if ($visit->technician_notes)
                                        <div class="mt-2 text-sm text-gray-700">{{ $visit->technician_notes }}</div>
                                    @endif
                                </div>
                            @empty
                                <div class="px-6 py-10 text-center text-gray-500">لم يتم تعيين زيارة بعد.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-100 font-semibold text-gray-900">قطع الغيار المستخدمة</div>
                        <div class="divide-y divide-gray-100">
                            @forelse ($serviceRequest->partsUsed as $partUsed)
                                <div class="px-6 py-4 flex items-center justify-between gap-4">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $partUsed->part?->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $partUsed->quantity }} × {{ number_format((float) $partUsed->unit_price, 2) }}</div>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900">{{ number_format($partUsed->quantity * (float) $partUsed->unit_price, 2) }}</div>
                                </div>
                            @empty
                                <div class="px-6 py-10 text-center text-gray-500">لا توجد قطع مستخدمة.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="flex flex-col gap-3 px-6 py-4 border-b border-gray-100 sm:flex-row sm:items-center sm:justify-between">
                            <div class="font-semibold text-gray-900">تقرير الصيانة</div>
                            @if ($serviceRequest->report)
                                <div class="flex flex-wrap gap-2">
                                    @can('generateReportPdf', $serviceRequest)
                                        <form method="POST" action="{{ route('service-requests.report.pdf', $serviceRequest) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                                                Generate PDF
                                            </button>
                                        </form>
                                    @endcan
                                    @if ($reportPdfUrl)
                                        <a href="{{ $reportPdfUrl }}" target="_blank" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                            عرض PDF
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if ($serviceRequest->report)
                            <div class="p-6 space-y-4">
                                <div>
                                    <div class="text-sm text-gray-500">الفني</div>
                                    <div class="mt-1 font-medium text-gray-900">{{ $serviceRequest->report->technician?->name }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">التشخيص</div>
                                    <p class="mt-1 text-gray-800 whitespace-pre-line">{{ $serviceRequest->report->diagnosis }}</p>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">الحل</div>
                                    <p class="mt-1 text-gray-800 whitespace-pre-line">{{ $serviceRequest->report->solution }}</p>
                                </div>
                                @if ($serviceRequest->report->customer_signature)
                                    <div>
                                        <div class="text-sm text-gray-500">توقيع العميل</div>
                                        <div class="mt-1 font-medium text-gray-900">{{ $serviceRequest->report->customer_signature }}</div>
                                    </div>
                                @endif
                            </div>
                        @else
                            @can('addReport', $serviceRequest)
                                <form method="POST" action="{{ route('service-requests.report.store', $serviceRequest) }}" class="p-6 space-y-4">
                                    @csrf
                                    @if (! Auth::user()->hasRole('technician'))
                                        <div>
                                            <label for="technician_id" class="block text-sm font-medium text-gray-700">الفني</label>
                                            <select id="technician_id" name="technician_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                @foreach ($technicians as $technician)
                                                    <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    <div>
                                        <label for="diagnosis" class="block text-sm font-medium text-gray-700">التشخيص</label>
                                        <textarea id="diagnosis" name="diagnosis" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('diagnosis') }}</textarea>
                                    </div>
                                    <div>
                                        <label for="solution" class="block text-sm font-medium text-gray-700">الحل</label>
                                        <textarea id="solution" name="solution" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('solution') }}</textarea>
                                    </div>
                                    <div>
                                        <label for="customer_signature" class="block text-sm font-medium text-gray-700">توقيع العميل</label>
                                        <input id="customer_signature" name="customer_signature" type="text" value="{{ old('customer_signature') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <button type="submit" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold text-white hover:bg-gray-700">
                                        إضافة التقرير
                                    </button>
                                </form>
                            @else
                                <div class="px-6 py-10 text-center text-gray-500">لم يتم إضافة تقرير بعد.</div>
                            @endcan
                        @endif
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                        <div class="font-semibold text-gray-900">الفاتورة</div>
                        @if ($serviceRequest->invoice)
                            <dl class="space-y-3 text-sm">
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
                            <a href="{{ route('invoices.show', $serviceRequest->invoice) }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                عرض الفاتورة
                            </a>
                        @else
                            @can('createInvoice', $serviceRequest)
                                <form method="POST" action="{{ route('service-requests.invoice.store', $serviceRequest) }}" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label for="service_cost" class="block text-sm font-medium text-gray-700">تكلفة الخدمة</label>
                                        <input id="service_cost" name="service_cost" type="number" min="0" step="0.01" value="{{ old('service_cost', '0.00') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500">تكلفة قطع الغيار</div>
                                        <div class="mt-1 font-medium text-gray-900">{{ number_format($partsTotal, 2) }}</div>
                                    </div>
                                    <div>
                                        <label for="tax_rate" class="block text-sm font-medium text-gray-700">الضريبة %</label>
                                        <input id="tax_rate" name="tax_rate" type="number" min="0" max="100" step="0.01" value="{{ old('tax_rate', '15') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label for="invoice_status" class="block text-sm font-medium text-gray-700">حالة الفاتورة</label>
                                        <select id="invoice_status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="draft" @selected(old('status', 'draft') === 'draft')>draft</option>
                                            <option value="issued" @selected(old('status') === 'issued')>issued</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                                        Create Invoice
                                    </button>
                                </form>
                            @else
                                <div class="text-sm text-gray-500">لا توجد فاتورة مرتبطة بهذا الطلب.</div>
                            @endcan
                        @endif
                    </div>

                    @can('changeStatus', $serviceRequest)
                        <form method="POST" action="{{ route('service-requests.change-status', $serviceRequest) }}" class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                            @csrf
                            @method('PATCH')
                            <div class="font-semibold text-gray-900">تغيير الحالة</div>
                            @if (count($availableStatuses) > 0)
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">الحالة الجديدة</label>
                                    <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach ($availableStatuses as $status)
                                            <option value="{{ $status }}">{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700">ملاحظة</label>
                                    <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                                </div>
                                <button type="submit" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold text-white hover:bg-gray-700">
                                    تحديث الحالة
                                </button>
                            @else
                                <div class="text-sm text-gray-500">لا توجد انتقالات متاحة من الحالة الحالية.</div>
                            @endif
                        </form>
                    @endcan

                    @can('assignTechnicians', $serviceRequest)
                        <form method="POST" action="{{ route('service-requests.assign-technician', $serviceRequest) }}" class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                            @csrf
                            <div class="font-semibold text-gray-900">تعيين فني</div>
                            <div>
                                <label for="assign_technician_id" class="block text-sm font-medium text-gray-700">الفني</label>
                                <select id="assign_technician_id" name="technician_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach ($technicians as $technician)
                                        <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="scheduled_at" class="block text-sm font-medium text-gray-700">موعد الزيارة</label>
                                <input id="scheduled_at" name="scheduled_at" type="datetime-local" value="{{ old('scheduled_at') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                            <div>
                                <label for="location_address" class="block text-sm font-medium text-gray-700">عنوان الزيارة</label>
                                <textarea id="location_address" name="location_address" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('location_address', $serviceRequest->customer?->address) }}</textarea>
                            </div>
                            <div>
                                <label for="technician_notes" class="block text-sm font-medium text-gray-700">ملاحظات للفني</label>
                                <textarea id="technician_notes" name="technician_notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('technician_notes') }}</textarea>
                            </div>
                            <button type="submit" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold text-white hover:bg-gray-700">
                                تعيين
                            </button>
                        </form>
                    @endcan

                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-100 font-semibold text-gray-900">Timeline</div>
                        <div class="p-6 space-y-5">
                            @forelse ($timeline as $event)
                                <div class="relative ps-5 border-s border-gray-200">
                                    <div class="absolute -start-1.5 top-1 h-3 w-3 rounded-full bg-gray-800"></div>
                                    <div class="text-sm font-medium text-gray-900">{{ str_replace('_', ' ', $event->action) }}</div>
                                    <div class="mt-1 text-xs text-gray-500">{{ $event->created_at->format('Y-m-d H:i') }} بواسطة {{ $event->user?->name ?? 'النظام' }}</div>
                                    @if ($event->new_values)
                                        <pre class="mt-2 text-xs bg-gray-50 rounded-md p-3 overflow-x-auto">{{ json_encode($event->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    @endif
                                </div>
                            @empty
                                <div class="text-sm text-gray-500">لا توجد أحداث بعد.</div>
                            @endforelse
                        </div>
                    </div>

                    @can('delete', $serviceRequest)
                        <form method="POST" action="{{ route('service-requests.destroy', $serviceRequest) }}" class="bg-white rounded-lg shadow-sm p-6">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-xs font-semibold text-white hover:bg-red-700">
                                حذف الطلب
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
