<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="__('ui.dashboards.technician_title')" :subtitle="__('ui.dashboards.technician_subtitle')">
            <x-slot name="actions">
                <x-ui.button :href="route('technician.visits.index')" variant="secondary">{{ __('ui.nav.my_visits') }}</x-ui.button>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="ff-page">
        <div class="ff-container space-y-6">
            @if (session('status'))
                <div class="ff-alert-success">{{ session('status') }}</div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-ui.stat-card :title="__('ui.dashboards.today_visits')" :value="number_format($stats['todayVisits'])" />
                <x-ui.stat-card :title="__('ui.dashboards.upcoming_visits')" :value="number_format($stats['upcomingVisits'])" tone="info" />
                <x-ui.stat-card :title="__('ui.dashboards.in_progress_requests')" :value="number_format($stats['inProgressRequests'])" tone="warning" />
                <x-ui.stat-card :title="__('ui.dashboards.waiting_parts_requests')" :value="number_format($stats['waitingPartsRequests'])" tone="danger" />
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <x-ui.card padding="p-0">
                    <x-slot name="header">
                        <div class="flex items-center justify-between gap-4">
                            <h3 class="ff-section-title">{{ __('ui.dashboards.today_visits') }}</h3>
                            <a href="{{ route('technician.visits.index', ['date' => today()->toDateString()]) }}" class="text-sm font-medium text-cyan-700 hover:text-cyan-900">{{ __('ui.visits.show_all') }}</a>
                        </div>
                    </x-slot>

                    <div class="divide-y divide-slate-100">
                        @forelse ($todayVisits as $visit)
                            <a href="{{ route('technician.visits.show', $visit) }}" class="block px-5 py-4 hover:bg-slate-50">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="font-medium text-slate-950">{{ $visit->serviceRequest?->title }}</div>
                                    <x-ui.badge :status="$visit->visit_status" />
                                </div>
                                <div class="mt-1 text-sm text-slate-500">
                                    {{ optional($visit->scheduled_at)->format('Y-m-d H:i') }} - {{ $visit->serviceRequest?->customer?->name }}
                                </div>
                            </a>
                        @empty
                            <div class="p-5">
                                <x-ui.empty-state :title="__('ui.visits.no_today_visits')" />
                            </div>
                        @endforelse
                    </div>
                </x-ui.card>

                <x-ui.card padding="p-0">
                    <x-slot name="header">
                        <div class="flex items-center justify-between gap-4">
                            <h3 class="ff-section-title">{{ __('ui.dashboards.upcoming_visits') }}</h3>
                            <a href="{{ route('technician.visits.index') }}" class="text-sm font-medium text-cyan-700 hover:text-cyan-900">{{ __('ui.visits.all_visits') }}</a>
                        </div>
                    </x-slot>

                    <div class="divide-y divide-slate-100">
                        @forelse ($upcomingVisits as $visit)
                            <a href="{{ route('technician.visits.show', $visit) }}" class="block px-5 py-4 hover:bg-slate-50">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="font-medium text-slate-950">{{ $visit->serviceRequest?->title }}</div>
                                    <x-ui.badge :status="$visit->visit_status" />
                                </div>
                                <div class="mt-1 text-sm text-slate-500">
                                    {{ optional($visit->scheduled_at)->format('Y-m-d H:i') }} - {{ $visit->location_address }}
                                </div>
                            </a>
                        @empty
                            <div class="p-5">
                                <x-ui.empty-state :title="__('ui.visits.no_upcoming_visits')" />
                            </div>
                        @endforelse
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-app-layout>
