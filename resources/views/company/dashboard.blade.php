<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="$title.' - '.$company->name" :subtitle="$subtitle">
            <x-slot name="actions">
                <x-ui.button :href="route('service-requests.index')" variant="secondary">{{ __('ui.nav.service_requests') }}</x-ui.button>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="ff-page">
        <div class="ff-container space-y-6">
            @if (session('status'))
                <div class="ff-alert-success">{{ session('status') }}</div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <x-ui.stat-card :title="__('ui.dashboards.requests_today')" :value="number_format($stats['requestsToday'])" />
                <x-ui.stat-card :title="__('ui.dashboards.open_requests')" :value="number_format($stats['openRequests'])" tone="info" />
                <x-ui.stat-card :title="__('ui.dashboards.completed_this_month')" :value="number_format($stats['completedThisMonth'])" tone="success" />
                <x-ui.stat-card :title="__('ui.dashboards.overdue_requests')" :value="number_format($stats['overdueRequests'])" tone="danger" />
                <x-ui.stat-card :title="__('ui.dashboards.active_technicians')" :value="number_format($stats['activeTechnicians'])" tone="warning" />
            </div>

            <x-ui.card :title="__('ui.dashboards.quick_links')">
                <div class="flex flex-wrap gap-3">
                    @foreach ($quickLinks as $link)
                        @if ($link['enabled'])
                            <x-ui.button :href="$link['url']" variant="secondary" size="sm">{{ $link['label'] }}</x-ui.button>
                        @else
                            <span class="ff-button ff-button-sm cursor-not-allowed border-slate-200 bg-slate-50 text-slate-400">
                                {{ $link['label'] }}
                            </span>
                        @endif
                    @endforeach
                </div>
            </x-ui.card>

            <div class="grid gap-6 xl:grid-cols-2">
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

                <x-ui.card :title="__('ui.dashboards.requests_by_priority')">
                    <div class="space-y-3">
                        @php($priorityMax = max(1, $requestsByPriority->max()))
                        @foreach ($requestsByPriority as $priority => $count)
                            <div>
                                <div class="mb-1 flex items-center justify-between gap-4 text-sm">
                                    <x-ui.badge :status="$priority" />
                                    <span class="font-medium text-slate-600">{{ number_format($count) }}</span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-100">
                                    <div class="h-2 rounded-full bg-emerald-600" style="width: {{ ($count / $priorityMax) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>
            </div>

            <div class="grid gap-6 xl:grid-cols-3">
                <x-ui.card :title="__('ui.dashboards.top_asset_types')" padding="p-0">
                    <div class="divide-y divide-slate-100">
                        @forelse ($topAssetTypes as $assetType)
                            <div class="flex items-center justify-between gap-4 px-5 py-4">
                                <div class="font-medium text-slate-950">{{ $assetType->type }}</div>
                                <x-ui.badge variant="info">{{ __('ui.dashboards.request_count', ['count' => number_format($assetType->total)]) }}</x-ui.badge>
                            </div>
                        @empty
                            <div class="p-5">
                                <x-ui.empty-state :title="__('ui.dashboards.no_asset_type_requests')" />
                            </div>
                        @endforelse
                    </div>
                </x-ui.card>

                <x-ui.card :title="__('ui.dashboards.top_technicians')" padding="p-0">
                    <div class="divide-y divide-slate-100">
                        @forelse ($topTechnicians as $technician)
                            <div class="flex items-center justify-between gap-4 px-5 py-4">
                                <div class="font-medium text-slate-950">{{ $technician->name }}</div>
                                <x-ui.badge variant="success">{{ __('ui.dashboards.completed_count', ['count' => number_format($technician->completed_requests)]) }}</x-ui.badge>
                            </div>
                        @empty
                            <div class="p-5">
                                <x-ui.empty-state :title="__('ui.dashboards.no_completed_technicians')" />
                            </div>
                        @endforelse
                    </div>
                </x-ui.card>

                <x-ui.card :title="__('ui.dashboards.low_stock_parts')" padding="p-0">
                    <div class="divide-y divide-slate-100">
                        @forelse ($lowStockParts as $part)
                            <div class="px-5 py-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="font-medium text-slate-950">{{ $part->name }}</div>
                                    <x-ui.badge variant="danger">{{ __('ui.dashboards.remaining_count', ['count' => number_format($part->quantity)]) }}</x-ui.badge>
                                </div>
                                <div class="mt-1 text-sm text-slate-500">{{ $part->sku }} - {{ __('ui.dashboards.stock_alert_limit', ['count' => number_format($part->low_stock_threshold)]) }}</div>
                            </div>
                        @empty
                            <div class="p-5">
                                <x-ui.empty-state :title="__('ui.dashboards.no_low_stock_parts')" />
                            </div>
                        @endforelse
                    </div>
                </x-ui.card>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <x-ui.table :title="__('ui.dashboards.latest_requests')" :columns="[__('ui.service_requests.title_field'), __('ui.service_requests.customer'), __('ui.service_requests.asset'), __('ui.service_requests.status'), '']">
                    @forelse ($recentRequests as $serviceRequest)
                        <tr>
                            <td>
                                <div class="font-medium text-slate-950">{{ $serviceRequest->title }}</div>
                                <div class="text-xs text-slate-500">#{{ $serviceRequest->id }}</div>
                            </td>
                            <td>{{ $serviceRequest->customer?->name }}</td>
                            <td>{{ $serviceRequest->serviceAsset?->name ?? __('ui.common.general_request') }}</td>
                            <td><x-ui.badge :status="$serviceRequest->status" /></td>
                            <td class="text-end">
                                <a href="{{ route('service-requests.show', $serviceRequest) }}" class="font-medium text-cyan-700 hover:text-cyan-900">{{ __('ui.actions.view') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-ui.empty-state :title="__('ui.dashboards.no_service_requests')" />
                            </td>
                        </tr>
                    @endforelse
                </x-ui.table>

                <x-ui.table :title="__('ui.dashboards.requests_need_attention')" :columns="[__('ui.service_requests.title_field'), __('ui.service_requests.customer'), __('ui.service_requests.preferred_date'), __('ui.service_requests.status')]">
                    @forelse ($overdueRequests as $serviceRequest)
                        <tr>
                            <td>
                                <a href="{{ route('service-requests.show', $serviceRequest) }}" class="font-medium text-slate-950 hover:text-cyan-800">
                                    {{ $serviceRequest->title }}
                                </a>
                            </td>
                            <td>{{ $serviceRequest->customer?->name }}</td>
                            <td class="text-red-700">{{ optional($serviceRequest->preferred_date)->format('Y-m-d') }}</td>
                            <td><x-ui.badge :status="$serviceRequest->status" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-ui.empty-state :title="__('ui.dashboards.no_overdue_requests')" />
                            </td>
                        </tr>
                    @endforelse
                </x-ui.table>
            </div>

            <div class="text-xs text-slate-400">
                {{ __('ui.dashboards.cache_notice', ['minutes' => $cacheMinutes]) }}
            </div>
        </div>
    </div>
</x-app-layout>
