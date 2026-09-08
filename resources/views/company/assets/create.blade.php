<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight" dir="rtl">
            إضافة جهاز
        </h2>
    </x-slot>

    <div class="py-8" dir="rtl">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('company.assets.store') }}" class="bg-white rounded-lg shadow-sm p-6 space-y-5">
                @csrf

                <div>
                    <label for="customer_id" class="block text-sm font-medium text-gray-700">العميل</label>
                    <select id="customer_id" name="customer_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                        <label for="name" class="block text-sm font-medium text-gray-700">اسم الجهاز</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700">النوع</label>
                        <input id="type" name="type" type="text" value="{{ old('type') }}" required placeholder="air_conditioner" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>
                    <div>
                        <label for="brand" class="block text-sm font-medium text-gray-700">الماركة</label>
                        <input id="brand" name="brand" type="text" value="{{ old('brand') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <x-input-error :messages="$errors->get('brand')" class="mt-2" />
                    </div>
                    <div>
                        <label for="model" class="block text-sm font-medium text-gray-700">الموديل</label>
                        <input id="model" name="model" type="text" value="{{ old('model') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <x-input-error :messages="$errors->get('model')" class="mt-2" />
                    </div>
                    <div>
                        <label for="serial_number" class="block text-sm font-medium text-gray-700">الرقم التسلسلي</label>
                        <input id="serial_number" name="serial_number" type="text" value="{{ old('serial_number') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <x-input-error :messages="$errors->get('serial_number')" class="mt-2" />
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">الحالة</label>
                        <select id="status" name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(old('status', 'active') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="purchase_date" class="block text-sm font-medium text-gray-700">تاريخ الشراء</label>
                        <input id="purchase_date" name="purchase_date" type="date" value="{{ old('purchase_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="warranty_start_date" class="block text-sm font-medium text-gray-700">بداية الضمان</label>
                        <input id="warranty_start_date" name="warranty_start_date" type="date" value="{{ old('warranty_start_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="warranty_end_date" class="block text-sm font-medium text-gray-700">نهاية الضمان</label>
                        <input id="warranty_end_date" name="warranty_end_date" type="date" value="{{ old('warranty_end_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">ملاحظات</label>
                    <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        حفظ الجهاز
                    </button>
                    <a href="{{ route('company.dashboard') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        العودة
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
