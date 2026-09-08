@if ($errors->any())
    <div class="ff-alert-danger">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if ($companies->isNotEmpty())
    <div>
        <label for="company_id" class="ff-form-label">{{ __('ui.service_requests.company') }}</label>
        <select id="company_id" name="company_id" class="ff-form-input">
            @foreach ($companies as $company)
                <option value="{{ $company->id }}" @selected(old('company_id', $serviceRequest->company_id ?? '') == $company->id)>{{ $company->name }}</option>
            @endforeach
        </select>
    </div>
@endif

<div>
    <label for="customer_id" class="ff-form-label">{{ __('ui.service_requests.customer') }}</label>
    <select id="customer_id" name="customer_id" class="ff-form-input">
        @foreach ($customers as $customer)
            <option value="{{ $customer->id }}" @selected(old('customer_id', $serviceRequest->customer_id ?? '') == $customer->id)>
                {{ $customer->name }} @if ($customer->company) - {{ $customer->company->name }} @endif
            </option>
        @endforeach
    </select>
</div>

<div>
    <label for="service_asset_id" class="ff-form-label">{{ __('ui.service_requests.asset') }}</label>
    <select id="service_asset_id" name="service_asset_id" class="ff-form-input">
        <option value="">{{ __('ui.service_requests.asset_optional') }}</option>
        @foreach ($assets as $asset)
            <option value="{{ $asset->id }}" @selected(old('service_asset_id', $serviceRequest->service_asset_id ?? '') == $asset->id)>
                {{ $asset->name }} - {{ $asset->serial_number }}
            </option>
        @endforeach
    </select>
</div>

<div>
    <label for="title" class="ff-form-label">{{ __('ui.service_requests.title_field') }}</label>
    <x-ui.input id="title" name="title" :value="old('title', $serviceRequest->title ?? '')" required />
</div>

<div>
    <label for="description" class="ff-form-label">{{ __('ui.service_requests.description') }}</label>
    <textarea id="description" name="description" rows="5" class="ff-form-input">{{ old('description', $serviceRequest->description ?? '') }}</textarea>
</div>

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label for="priority" class="ff-form-label">{{ __('ui.service_requests.priority') }}</label>
        <select id="priority" name="priority" class="ff-form-input">
            @foreach ($priorities as $priority)
                <option value="{{ $priority }}" @selected(old('priority', $serviceRequest->priority ?? 'medium') === $priority)>{{ __('ui.priorities.'.$priority) }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="preferred_date" class="ff-form-label">{{ __('ui.service_requests.preferred_date') }}</label>
        <x-ui.input id="preferred_date" name="preferred_date" type="date" :value="old('preferred_date', optional($serviceRequest->preferred_date ?? null)->format('Y-m-d'))" />
    </div>
</div>

<div class="flex items-center gap-3">
    <x-ui.button type="submit">{{ __('ui.actions.save_request') }}</x-ui.button>
    <x-ui.button :href="route('service-requests.index')" variant="secondary">{{ __('ui.actions.cancel') }}</x-ui.button>
</div>
