<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="إضافة جهاز" subtitle="ربط جهاز جديد بعميل وتوليد QR تلقائيًا." dir="rtl" />
    </x-slot>

    <div class="ff-page" dir="rtl">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="ff-alert-success mb-6">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('company.assets.store') }}" class="ff-card p-6 space-y-5">
                @csrf

                <div>
                    <label for="customer_id" class="ff-form-label">العميل</label>
                    <select id="customer_id" name="customer_id" required class="ff-form-input">
                        <option value="">اختر العميل</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected((string) old('customer_id', $selectedCustomerId) === (string) $customer->id)>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('customer_id')" class="mt-2" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="name" class="ff-form-label">اسم الجهاز</label>
                        <x-ui.input id="name" name="name" value="{{ old('name') }}" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <label for="type" class="ff-form-label">النوع</label>
                        <x-ui.input id="type" name="type" value="{{ old('type') }}" required placeholder="air_conditioner" />
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>
                    <div>
                        <label for="brand" class="ff-form-label">الماركة</label>
                        <x-ui.input id="brand" name="brand" value="{{ old('brand') }}" />
                        <x-input-error :messages="$errors->get('brand')" class="mt-2" />
                    </div>
                    <div>
                        <label for="model" class="ff-form-label">الموديل</label>
                        <x-ui.input id="model" name="model" value="{{ old('model') }}" />
                        <x-input-error :messages="$errors->get('model')" class="mt-2" />
                    </div>
                    <div>
                        <label for="serial_number" class="ff-form-label">الرقم التسلسلي</label>
                        <x-ui.input id="serial_number" name="serial_number" value="{{ old('serial_number') }}" required />
                        <x-input-error :messages="$errors->get('serial_number')" class="mt-2" />
                    </div>
                    <div>
                        <label for="status" class="ff-form-label">الحالة</label>
                        <select id="status" name="status" required class="ff-form-input">
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(old('status', 'active') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="purchase_date" class="ff-form-label">تاريخ الشراء</label>
                        <x-ui.input id="purchase_date" name="purchase_date" type="date" value="{{ old('purchase_date') }}" />
                    </div>
                    <div>
                        <label for="warranty_start_date" class="ff-form-label">بداية الضمان</label>
                        <x-ui.input id="warranty_start_date" name="warranty_start_date" type="date" value="{{ old('warranty_start_date') }}" />
                    </div>
                    <div>
                        <label for="warranty_end_date" class="ff-form-label">نهاية الضمان</label>
                        <x-ui.input id="warranty_end_date" name="warranty_end_date" type="date" value="{{ old('warranty_end_date') }}" />
                    </div>
                </div>

                <div>
                    <label for="notes" class="ff-form-label">ملاحظات</label>
                    <textarea id="notes" name="notes" rows="3" class="ff-form-input">{{ old('notes') }}</textarea>
                </div>

                <div class="flex gap-3">
                    <x-ui.button type="submit">حفظ الجهاز</x-ui.button>
                    <x-ui.button :href="route('company.dashboard')" variant="secondary">العودة</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
