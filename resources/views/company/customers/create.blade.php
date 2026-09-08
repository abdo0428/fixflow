<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="إضافة عميل" subtitle="تسجيل عميل جديد داخل شركتك." dir="rtl" />
    </x-slot>

    <div class="ff-page" dir="rtl">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('company.customers.store') }}" class="ff-card p-6 space-y-5">
                @csrf

                <div>
                    <label for="name" class="ff-form-label">اسم العميل</label>
                    <x-ui.input id="name" name="name" value="{{ old('name') }}" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="email" class="ff-form-label">البريد</label>
                        <x-ui.input id="email" name="email" type="email" value="{{ old('email') }}" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <label for="phone" class="ff-form-label">الهاتف</label>
                        <x-ui.input id="phone" name="phone" value="{{ old('phone') }}" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <label for="address" class="ff-form-label">العنوان</label>
                    <textarea id="address" name="address" rows="3" class="ff-form-input">{{ old('address') }}</textarea>
                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                </div>

                <div>
                    <label for="city" class="ff-form-label">المدينة</label>
                    <x-ui.input id="city" name="city" value="{{ old('city') }}" />
                    <x-input-error :messages="$errors->get('city')" class="mt-2" />
                </div>

                <div>
                    <label for="notes" class="ff-form-label">ملاحظات</label>
                    <textarea id="notes" name="notes" rows="3" class="ff-form-input">{{ old('notes') }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>

                <div class="flex gap-3">
                    <x-ui.button type="submit">حفظ العميل</x-ui.button>
                    <x-ui.button :href="route('company.dashboard')" variant="secondary">العودة</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
