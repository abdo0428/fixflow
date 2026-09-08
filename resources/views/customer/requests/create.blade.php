<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="طلب صيانة جديد" subtitle="اختر الجهاز وأرسل تفاصيل المشكلة لفريق الصيانة." dir="rtl" />
    </x-slot>

    <div class="ff-page" dir="rtl">
        <div class="ff-container max-w-3xl">
            <x-ui.card title="تفاصيل الطلب">
                <form method="POST" action="{{ route('customer.service-requests.store') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                    <div>
                        <label for="service_asset_id" class="ff-form-label">الجهاز</label>
                        <select id="service_asset_id" name="service_asset_id" required class="ff-form-input">
                            <option value="">اختر الجهاز</option>
                            @foreach ($assets as $asset)
                                <option value="{{ $asset->id }}" @selected((int) old('service_asset_id') === (int) $asset->id)>
                                    {{ $asset->name }} - {{ $asset->serial_number }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('service_asset_id')" class="mt-2" />
                    </div>

                    <div>
                        <label for="title" class="ff-form-label">عنوان المشكلة</label>
                        <x-ui.input id="title" name="title" :value="old('title')" required maxlength="255" />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div>
                        <label for="description" class="ff-form-label">وصف المشكلة</label>
                        <textarea id="description" name="description" rows="5" class="ff-form-input">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="preferred_date" class="ff-form-label">تاريخ الزيارة المفضل</label>
                            <x-ui.input id="preferred_date" name="preferred_date" type="date" :value="old('preferred_date')" />
                            <x-input-error :messages="$errors->get('preferred_date')" class="mt-2" />
                        </div>
                        <div>
                            <label for="priority" class="ff-form-label">الأولوية</label>
                            <select id="priority" name="priority" required class="ff-form-input">
                                @foreach ($priorities as $priority)
                                    <option value="{{ $priority }}" @selected(old('priority', 'medium') === $priority)>
                                        {{ $priorityLabels[$priority] ?? $priority }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('priority')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <x-ui.button type="submit">إرسال الطلب</x-ui.button>
                        <x-ui.button :href="route('customer.service-requests.index')" variant="secondary">إلغاء</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
