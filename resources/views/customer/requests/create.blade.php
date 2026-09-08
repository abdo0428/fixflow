<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            طلب صيانة جديد
        </h2>
    </x-slot>

    <div class="py-8" dir="rtl">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('customer.service-requests.store') }}" class="bg-white rounded-lg shadow-sm p-6 space-y-5">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                <div>
                    <label for="service_asset_id" class="block text-sm font-medium text-gray-700">الجهاز</label>
                    <select id="service_asset_id" name="service_asset_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                    <label for="title" class="block text-sm font-medium text-gray-700">عنوان المشكلة</label>
                    <input id="title" name="title" type="text" value="{{ old('title') }}" required maxlength="255" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">وصف المشكلة</label>
                    <textarea id="description" name="description" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="preferred_date" class="block text-sm font-medium text-gray-700">تاريخ الزيارة المفضل</label>
                        <input id="preferred_date" name="preferred_date" type="date" value="{{ old('preferred_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <x-input-error :messages="$errors->get('preferred_date')" class="mt-2" />
                    </div>
                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700">الأولوية</label>
                        <select id="priority" name="priority" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                    <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">إرسال الطلب</button>
                    <a href="{{ route('customer.service-requests.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

