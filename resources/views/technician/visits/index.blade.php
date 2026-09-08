<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="__('ui.visits.title')" :subtitle="__('ui.visits.subtitle')" />
    </x-slot>

    <div class="ff-page">
        <div class="ff-container space-y-6">
            <x-ui.card>
                <form method="GET" action="{{ route('technician.visits.index') }}">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label for="status" class="ff-form-label">{{ __('ui.service_requests.status_filter') }}</label>
                            <select id="status" name="status" class="ff-form-input">
                                <option value="">{{ __('ui.service_requests.all_statuses') }}</option>
                                @foreach ($visitStatuses as $status)
                                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                                        {{ __('ui.statuses.'.$status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="date" class="ff-form-label">{{ __('ui.service_requests.preferred_date') }}</label>
                            <x-ui.input id="date" name="date" type="date" :value="$filters['date'] ?? ''" />
                        </div>
                        <div class="flex items-end gap-2">
                            <x-ui.button type="submit">{{ __('ui.actions.filter') }}</x-ui.button>
                            <x-ui.button :href="route('technician.visits.index')" variant="secondary">{{ __('ui.actions.reset') }}</x-ui.button>
                        </div>
                    </div>
                </form>
            </x-ui.card>

            <x-ui.table
                :title="__('ui.visits.assigned_to_me')"
                :columns="[__('ui.service_requests.title_field'), __('ui.visits.customer'), __('ui.visits.asset'), __('ui.visits.scheduled_at'), __('ui.visits.visit_status'), '']"
                :footer="$visits->links()"
            >
                @forelse ($visits as $visit)
                    <tr>
                        <td class="font-medium text-slate-950">{{ $visit->serviceRequest?->title }}</td>
                        <td>{{ $visit->serviceRequest?->customer?->name }}</td>
                        <td>{{ $visit->serviceRequest?->serviceAsset?->name ?? __('ui.common.not_defined') }}</td>
                        <td>{{ optional($visit->scheduled_at)->format('Y-m-d H:i') }}</td>
                        <td><x-ui.badge :status="$visit->visit_status" /></td>
                        <td class="text-end">
                            <a href="{{ route('technician.visits.show', $visit) }}" class="font-medium text-cyan-700 hover:text-cyan-900">{{ __('ui.actions.details') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-ui.empty-state :title="__('ui.visits.no_matching_visits')" />
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>
    </div>
</x-app-layout>
