<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="__('ui.service_requests.title')" :subtitle="__('ui.service_requests.subtitle')">
            <x-slot name="actions">
                @can('create', App\Models\ServiceRequest::class)
                    <x-ui.button :href="route('service-requests.create')">{{ __('ui.actions.create_request') }}</x-ui.button>
                @endcan
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="ff-page">
        <div class="ff-container space-y-6">
            @if (session('status'))
                <div class="ff-alert-success">{{ session('status') }}</div>
            @endif

            <x-ui.card>
                <form method="GET" action="{{ route('service-requests.index') }}">
                    <div class="grid gap-4 md:grid-cols-4">
                        <div>
                            <label for="status" class="ff-form-label">{{ __('ui.service_requests.status_filter') }}</label>
                            <select id="status" name="status" class="ff-form-input">
                                <option value="">{{ __('ui.service_requests.all_statuses') }}</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ __('ui.statuses.'.$status) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="priority" class="ff-form-label">{{ __('ui.service_requests.priority_filter') }}</label>
                            <select id="priority" name="priority" class="ff-form-input">
                                <option value="">{{ __('ui.service_requests.all_priorities') }}</option>
                                @foreach ($priorities as $priority)
                                    <option value="{{ $priority }}" @selected($filters['priority'] === $priority)>{{ __('ui.priorities.'.$priority) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="technician_id" class="ff-form-label">{{ __('ui.service_requests.technician_filter') }}</label>
                            <select id="technician_id" name="technician_id" class="ff-form-input">
                                <option value="">{{ __('ui.service_requests.all_technicians') }}</option>
                                @foreach ($technicians as $technician)
                                    <option value="{{ $technician->id }}" @selected((string) $filters['technician_id'] === (string) $technician->id)>{{ $technician->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <x-ui.button type="submit">{{ __('ui.actions.filter') }}</x-ui.button>
                            <x-ui.button :href="route('service-requests.index')" variant="secondary">{{ __('ui.actions.reset') }}</x-ui.button>
                        </div>
                    </div>
                </form>
            </x-ui.card>

            <x-ui.table
                :columns="[__('ui.service_requests.title_field'), __('ui.service_requests.customer'), __('ui.service_requests.asset'), __('ui.service_requests.priority'), __('ui.service_requests.status'), __('ui.service_requests.technician'), '']"
                :footer="$serviceRequests->links()"
            >
                @forelse ($serviceRequests as $serviceRequest)
                    <tr>
                        <td>
                            <div class="font-medium text-slate-950">{{ $serviceRequest->title }}</div>
                            <div class="text-xs text-slate-500">#{{ $serviceRequest->id }}</div>
                        </td>
                        <td>{{ $serviceRequest->customer?->name }}</td>
                        <td>{{ $serviceRequest->serviceAsset?->name ?? __('ui.common.general_request') }}</td>
                        <td><x-ui.badge :status="$serviceRequest->priority" /></td>
                        <td><x-ui.badge :status="$serviceRequest->status" /></td>
                        <td>{{ $serviceRequest->visits->pluck('technician.name')->filter()->unique()->join(', ') ?: __('ui.common.not_defined') }}</td>
                        <td class="text-end">
                            <a href="{{ route('service-requests.show', $serviceRequest) }}" class="font-medium text-cyan-700 hover:text-cyan-900">{{ __('ui.actions.view') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-ui.empty-state :title="__('ui.service_requests.no_matching_requests')" />
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>
    </div>
</x-app-layout>
