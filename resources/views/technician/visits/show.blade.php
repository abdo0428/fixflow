<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                تفاصيل الزيارة
            </h2>
            <a href="{{ route('technician.visits.index') }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-900">العودة إلى زياراتي</a>
        </div>
    </x-slot>

    <div class="py-8" dir="rtl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 bg-white rounded-lg shadow-sm p-5 space-y-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="text-sm text-gray-500">طلب الصيانة</div>
                            <h3 class="mt-1 text-2xl font-semibold text-gray-900">{{ $serviceRequest->title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-gray-600">{{ $serviceRequest->description ?: 'لا يوجد وصف تفصيلي.' }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700">{{ $requestLabels[$serviceRequest->status] ?? $serviceRequest->status }}</span>
                            <span class="rounded-full bg-amber-50 px-3 py-1 text-sm text-amber-800">{{ $priorityLabels[$serviceRequest->priority] ?? $serviceRequest->priority }}</span>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <div class="text-sm text-gray-500">العميل</div>
                            <div class="mt-1 font-medium text-gray-900">{{ $serviceRequest->customer?->name }}</div>
                            <div class="text-sm text-gray-600">{{ $serviceRequest->customer?->phone }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">الجهاز</div>
                            <div class="mt-1 font-medium text-gray-900">{{ $serviceRequest->serviceAsset?->name ?? 'غير محدد' }}</div>
                            <div class="text-sm text-gray-600">{{ $serviceRequest->serviceAsset?->brand }} {{ $serviceRequest->serviceAsset?->model }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">موعد الزيارة</div>
                            <div class="mt-1 font-medium text-gray-900">{{ optional($visit->scheduled_at)->format('Y-m-d H:i') }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">الموقع</div>
                            <div class="mt-1 font-medium text-gray-900">{{ $visit->location_address ?: $serviceRequest->customer?->address }}</div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        @if ($canStart)
                            <form method="POST" action="{{ route('technician.visits.start', $visit) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">بدء الزيارة</button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="font-semibold text-gray-900">حالة الزيارة</div>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">الحالة</dt>
                            <dd class="font-medium text-gray-900">{{ $visitLabels[$visit->visit_status] ?? $visit->visit_status }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">بدأت</dt>
                            <dd class="font-medium text-gray-900">{{ optional($visit->started_at)->format('Y-m-d H:i') ?: 'لم تبدأ' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">انتهت</dt>
                            <dd class="font-medium text-gray-900">{{ optional($visit->finished_at)->format('Y-m-d H:i') ?: 'لم تنته' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="font-semibold text-gray-900">الملاحظات الفنية</div>
                    <form method="POST" action="{{ route('technician.visits.notes', $visit) }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PATCH')
                        <textarea name="technician_notes" rows="5" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('technician_notes', $visit->technician_notes) }}</textarea>
                        <x-input-error :messages="$errors->get('technician_notes')" />
                        <button type="submit" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">حفظ الملاحظات</button>
                    </form>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="font-semibold text-gray-900">قطع الغيار المستخدمة</div>
                    <div class="mt-4 divide-y divide-gray-100">
                        @forelse ($serviceRequest->partsUsed as $partUsed)
                            <div class="py-3 text-sm">
                                <div class="font-medium text-gray-900">{{ $partUsed->part?->name }}</div>
                                <div class="text-gray-600">الكمية: {{ $partUsed->quantity }} - السعر: {{ number_format((float) $partUsed->unit_price, 2) }}</div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-sm text-gray-500">لم تسجل قطع غيار لهذا الطلب بعد.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            @if ($canFinish)
                <form method="POST" action="{{ route('technician.visits.finish', $visit) }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow-sm p-5 space-y-5">
                    @csrf
                    <div>
                        <div class="font-semibold text-gray-900">إنهاء الزيارة وإغلاق الطلب</div>
                        <p class="mt-1 text-sm text-gray-500">أدخل التشخيص والحل النهائي، ويمكنك إرفاق صور قبل وبعد وإضافة قطع مستخدمة.</p>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2">
                        <div>
                            <label for="diagnosis" class="block text-sm font-medium text-gray-700">التشخيص</label>
                            <textarea id="diagnosis" name="diagnosis" rows="5" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('diagnosis', $serviceRequest->report?->diagnosis) }}</textarea>
                            <x-input-error :messages="$errors->get('diagnosis')" class="mt-2" />
                        </div>
                        <div>
                            <label for="solution" class="block text-sm font-medium text-gray-700">الحل</label>
                            <textarea id="solution" name="solution" rows="5" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('solution', $serviceRequest->report?->solution) }}</textarea>
                            <x-input-error :messages="$errors->get('solution')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <div class="text-sm font-medium text-gray-700">قطع مستخدمة</div>
                        <div class="mt-2 grid gap-3">
                            @for ($index = 0; $index < 3; $index++)
                                <div class="grid gap-3 md:grid-cols-3">
                                    <select name="parts[{{ $index }}][part_id]" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">اختر قطعة</option>
                                        @foreach ($parts as $part)
                                            <option value="{{ $part->id }}" @selected((int) old("parts.{$index}.part_id") === (int) $part->id)>
                                                {{ $part->name }} - {{ $part->sku }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input name="parts[{{ $index }}][quantity]" type="number" min="1" value="{{ old("parts.{$index}.quantity", 1) }}" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="الكمية">
                                    <input name="parts[{{ $index }}][unit_price]" type="number" min="0" step="0.01" value="{{ old("parts.{$index}.unit_price") }}" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="سعر الوحدة اختياري">
                                </div>
                            @endfor
                        </div>
                        <x-input-error :messages="$errors->get('parts')" class="mt-2" />
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2">
                        <div>
                            <label for="before_images" class="block text-sm font-medium text-gray-700">صور قبل الصيانة</label>
                            <input id="before_images" name="before_images[]" type="file" multiple accept="image/*" class="mt-1 block w-full text-sm text-gray-700">
                            <x-input-error :messages="$errors->get('before_images')" class="mt-2" />
                        </div>
                        <div>
                            <label for="after_images" class="block text-sm font-medium text-gray-700">صور بعد الصيانة</label>
                            <input id="after_images" name="after_images[]" type="file" multiple accept="image/*" class="mt-1 block w-full text-sm text-gray-700">
                            <x-input-error :messages="$errors->get('after_images')" class="mt-2" />
                        </div>
                    </div>

                    <button type="submit" class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700">إنهاء الزيارة وإغلاق الطلب</button>
                </form>
            @endif

            <div class="bg-white rounded-lg shadow-sm p-5">
                <div class="font-semibold text-gray-900">Timeline</div>
                <div class="mt-4 space-y-4">
                    @forelse ($timeline as $event)
                        <div class="border-r-2 border-indigo-200 pr-4">
                            <div class="text-sm font-medium text-gray-900">{{ $event->action }}</div>
                            <div class="mt-1 text-xs text-gray-500">{{ optional($event->created_at)->format('Y-m-d H:i') }} - {{ $event->user?->name ?? 'System' }}</div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-sm text-gray-500">لا توجد أحداث مسجلة لهذا الطلب بعد.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

