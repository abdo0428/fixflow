<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="تفاصيل الزيارة" subtitle="بيانات الطلب والعميل وإجراءات الفني." dir="rtl">
            <x-slot name="actions">
                <x-ui.button :href="route('technician.visits.index')" variant="secondary">العودة إلى زياراتي</x-ui.button>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="ff-page" dir="rtl">
        <div class="ff-container space-y-6">
            @if (session('status'))
                <div class="ff-alert-success">{{ session('status') }}</div>
            @endif

            <div class="grid gap-6 lg:grid-cols-3">
                <x-ui.card class="lg:col-span-2">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="text-sm text-slate-500">طلب الصيانة</div>
                            <h3 class="mt-1 text-2xl font-semibold text-slate-950">{{ $serviceRequest->title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $serviceRequest->description ?: 'لا يوجد وصف تفصيلي.' }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-ui.badge :status="$serviceRequest->status">{{ $requestLabels[$serviceRequest->status] ?? $serviceRequest->status }}</x-ui.badge>
                            <x-ui.badge :status="$serviceRequest->priority">{{ $priorityLabels[$serviceRequest->priority] ?? $serviceRequest->priority }}</x-ui.badge>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div>
                            <div class="text-sm text-slate-500">العميل</div>
                            <div class="mt-1 font-medium text-slate-950">{{ $serviceRequest->customer?->name }}</div>
                            <div class="text-sm text-slate-600">{{ $serviceRequest->customer?->phone }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-slate-500">الجهاز</div>
                            <div class="mt-1 font-medium text-slate-950">{{ $serviceRequest->serviceAsset?->name ?? 'غير محدد' }}</div>
                            <div class="text-sm text-slate-600">{{ $serviceRequest->serviceAsset?->brand }} {{ $serviceRequest->serviceAsset?->model }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-slate-500">موعد الزيارة</div>
                            <div class="mt-1 font-medium text-slate-950">{{ optional($visit->scheduled_at)->format('Y-m-d H:i') }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-slate-500">الموقع</div>
                            <div class="mt-1 font-medium text-slate-950">{{ $visit->location_address ?: $serviceRequest->customer?->address }}</div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3">
                        @if ($canStart)
                            <form method="POST" action="{{ route('technician.visits.start', $visit) }}">
                                @csrf
                                <x-ui.button type="submit">بدء الزيارة</x-ui.button>
                            </form>
                        @endif
                    </div>
                </x-ui.card>

                <x-ui.card title="حالة الزيارة">
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">الحالة</dt>
                            <dd><x-ui.badge :status="$visit->visit_status">{{ $visitLabels[$visit->visit_status] ?? $visit->visit_status }}</x-ui.badge></dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">بدأت</dt>
                            <dd class="font-medium text-slate-950">{{ optional($visit->started_at)->format('Y-m-d H:i') ?: 'لم تبدأ' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">انتهت</dt>
                            <dd class="font-medium text-slate-950">{{ optional($visit->finished_at)->format('Y-m-d H:i') ?: 'لم تنته' }}</dd>
                        </div>
                    </dl>
                </x-ui.card>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <x-ui.card title="الملاحظات الفنية">
                    <form method="POST" action="{{ route('technician.visits.notes', $visit) }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PATCH')
                        <textarea name="technician_notes" rows="5" class="ff-form-input">{{ old('technician_notes', $visit->technician_notes) }}</textarea>
                        <x-input-error :messages="$errors->get('technician_notes')" />
                        <x-ui.button type="submit" variant="secondary">حفظ الملاحظات</x-ui.button>
                    </form>
                </x-ui.card>

                <x-ui.card title="قطع الغيار المستخدمة" padding="p-0">
                    <div class="divide-y divide-slate-100">
                        @forelse ($serviceRequest->partsUsed as $partUsed)
                            <div class="px-5 py-4 text-sm">
                                <div class="font-medium text-slate-950">{{ $partUsed->part?->name }}</div>
                                <div class="text-slate-600">الكمية: {{ $partUsed->quantity }} - السعر: {{ number_format((float) $partUsed->unit_price, 2) }}</div>
                            </div>
                        @empty
                            <div class="p-5">
                                <x-ui.empty-state title="لم تسجل قطع غيار لهذا الطلب بعد." />
                            </div>
                        @endforelse
                    </div>
                </x-ui.card>
            </div>

            @if ($canFinish)
                <x-ui.card title="إنهاء الزيارة وإغلاق الطلب" subtitle="أدخل التشخيص والحل النهائي، ويمكنك إرفاق صور قبل وبعد وإضافة قطع مستخدمة.">
                    <form method="POST" action="{{ route('technician.visits.finish', $visit) }}" enctype="multipart/form-data" class="space-y-5">
                        @csrf

                        <div class="grid gap-4 lg:grid-cols-2">
                            <div>
                                <label for="diagnosis" class="ff-form-label">التشخيص</label>
                                <textarea id="diagnosis" name="diagnosis" rows="5" required class="ff-form-input">{{ old('diagnosis', $serviceRequest->report?->diagnosis) }}</textarea>
                                <x-input-error :messages="$errors->get('diagnosis')" class="mt-2" />
                            </div>
                            <div>
                                <label for="solution" class="ff-form-label">الحل</label>
                                <textarea id="solution" name="solution" rows="5" required class="ff-form-input">{{ old('solution', $serviceRequest->report?->solution) }}</textarea>
                                <x-input-error :messages="$errors->get('solution')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <div class="ff-form-label">قطع مستخدمة</div>
                            <div class="mt-2 grid gap-3">
                                @for ($index = 0; $index < 3; $index++)
                                    <div class="grid gap-3 md:grid-cols-3">
                                        <select name="parts[{{ $index }}][part_id]" class="ff-form-input">
                                            <option value="">اختر قطعة</option>
                                            @foreach ($parts as $part)
                                                <option value="{{ $part->id }}" @selected((int) old("parts.{$index}.part_id") === (int) $part->id)>
                                                    {{ $part->name }} - {{ $part->sku }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-ui.input name="parts[{{ $index }}][quantity]" type="number" min="1" :value="old('parts.'.$index.'.quantity', 1)" placeholder="الكمية" />
                                        <x-ui.input name="parts[{{ $index }}][unit_price]" type="number" min="0" step="0.01" :value="old('parts.'.$index.'.unit_price')" placeholder="سعر الوحدة اختياري" />
                                    </div>
                                @endfor
                            </div>
                            <x-input-error :messages="$errors->get('parts')" class="mt-2" />
                        </div>

                        <div class="grid gap-4 lg:grid-cols-2">
                            <div>
                                <label for="before_images" class="ff-form-label">صور قبل الصيانة</label>
                                <input id="before_images" name="before_images[]" type="file" multiple accept="image/*" class="ff-form-input file:ml-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-slate-700">
                                <x-input-error :messages="$errors->get('before_images')" class="mt-2" />
                            </div>
                            <div>
                                <label for="after_images" class="ff-form-label">صور بعد الصيانة</label>
                                <input id="after_images" name="after_images[]" type="file" multiple accept="image/*" class="ff-form-input file:ml-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-slate-700">
                                <x-input-error :messages="$errors->get('after_images')" class="mt-2" />
                            </div>
                        </div>

                        <x-ui.button type="submit" variant="success">إنهاء الزيارة وإغلاق الطلب</x-ui.button>
                    </form>
                </x-ui.card>
            @endif

            <x-ui.card title="Timeline">
                <x-ui.status-timeline :items="$timeline" />
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
