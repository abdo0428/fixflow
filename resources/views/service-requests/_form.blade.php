@if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if ($companies->isNotEmpty())
    <div>
        <label for="company_id" class="block text-sm font-medium text-gray-700">الشركة</label>
        <select id="company_id" name="company_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach ($companies as $company)
                <option value="{{ $company->id }}" @selected(old('company_id', $serviceRequest->company_id ?? '') == $company->id)>{{ $company->name }}</option>
            @endforeach
        </select>
    </div>
@endif

<div>
    <label for="customer_id" class="block text-sm font-medium text-gray-700">العميل</label>
    <select id="customer_id" name="customer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @foreach ($customers as $customer)
            <option value="{{ $customer->id }}" @selected(old('customer_id', $serviceRequest->customer_id ?? '') == $customer->id)>
                {{ $customer->name }} @if ($customer->company) - {{ $customer->company->name }} @endif
            </option>
        @endforeach
    </select>
</div>

<div>
    <label for="service_asset_id" class="block text-sm font-medium text-gray-700">الجهاز</label>
    <select id="service_asset_id" name="service_asset_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">طلب عام بدون جهاز</option>
        @foreach ($assets as $asset)
            <option value="{{ $asset->id }}" @selected(old('service_asset_id', $serviceRequest->service_asset_id ?? '') == $asset->id)>
                {{ $asset->name }} - {{ $asset->serial_number }}
            </option>
        @endforeach
    </select>
</div>

<div>
    <label for="title" class="block text-sm font-medium text-gray-700">عنوان الطلب</label>
    <input id="title" name="title" type="text" value="{{ old('title', $serviceRequest->title ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
</div>

<div>
    <label for="description" class="block text-sm font-medium text-gray-700">الوصف</label>
    <textarea id="description" name="description" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $serviceRequest->description ?? '') }}</textarea>
</div>

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label for="priority" class="block text-sm font-medium text-gray-700">الأولوية</label>
        <select id="priority" name="priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach ($priorities as $priority)
                <option value="{{ $priority }}" @selected(old('priority', $serviceRequest->priority ?? 'medium') === $priority)>{{ $priority }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="preferred_date" class="block text-sm font-medium text-gray-700">التاريخ المفضل</label>
        <input id="preferred_date" name="preferred_date" type="date" value="{{ old('preferred_date', optional($serviceRequest->preferred_date ?? null)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
</div>

<div class="flex items-center gap-3">
    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 rounded-md text-xs font-semibold uppercase text-white hover:bg-gray-700">
        حفظ
    </button>
    <a href="{{ route('service-requests.index') }}" class="text-sm text-gray-600 hover:text-gray-900">إلغاء</a>
</div>
