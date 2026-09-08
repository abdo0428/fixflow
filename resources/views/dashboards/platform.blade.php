<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            :title="__('ui.dashboards.platform_title')"
            :subtitle="__('ui.dashboards.platform_subtitle')"
        />
    </x-slot>

    <div class="ff-page">
        <div class="ff-container space-y-6">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-ui.stat-card :title="__('ui.dashboards.companies_count')" :value="number_format($stats['companies'])" />
                <x-ui.stat-card :title="__('ui.dashboards.active_companies')" :value="number_format($stats['activeCompanies'])" tone="success" />
                <x-ui.stat-card :title="__('ui.dashboards.users_count')" :value="number_format($stats['users'])" tone="info" />
                <x-ui.stat-card :title="__('ui.dashboards.system_requests')" :value="number_format($stats['serviceRequests'])" tone="warning" />
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <x-ui.card :title="__('ui.dashboards.requests_by_status')">
                    <div class="space-y-3">
                        @php($statusMax = max(1, $requestsByStatus->max()))
                        @foreach ($requestsByStatus as $status => $count)
                            <div>
                                <div class="mb-1 flex items-center justify-between gap-4 text-sm">
                                    <x-ui.badge :status="$status" />
                                    <span class="font-medium text-slate-600">{{ number_format($count) }}</span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-100">
                                    <div class="h-2 rounded-full bg-cyan-700" style="width: {{ ($count / $statusMax) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>

                <x-ui.table :title="__('ui.dashboards.latest_companies')" :columns="[__('ui.nav.company'), __('ui.auth.email'), __('ui.service_requests.status')]">
                    @forelse ($companies as $company)
                        <tr>
                            <td class="font-medium text-slate-950">{{ $company->name }}</td>
                            <td>{{ $company->email ?? __('ui.dashboards.no_email') }}</td>
                            <td><x-ui.badge :status="$company->status" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <x-ui.empty-state :title="__('ui.dashboards.no_companies')" />
                            </td>
                        </tr>
                    @endforelse
                </x-ui.table>
            </div>
        </div>
    </div>
</x-app-layout>
