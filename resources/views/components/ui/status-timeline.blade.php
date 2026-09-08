@props([
    'items' => [],
    'emptyTitle' => null,
])

@php
    $events = collect($items);
@endphp

<div {{ $attributes->merge(['class' => 'space-y-5']) }}>
    @forelse ($events as $event)
        @php
            $action = data_get($event, 'action');
            $createdAt = data_get($event, 'created_at');
            $userName = data_get($event, 'user.name') ?: 'النظام';
            $newValues = data_get($event, 'new_values');
        @endphp

        <div class="relative border-s border-slate-200 ps-5">
            <div class="absolute -start-1.5 top-1 h-3 w-3 rounded-full bg-cyan-700 ring-4 ring-white"></div>
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.badge variant="primary">{{ str_replace('_', ' ', (string) $action) }}</x-ui.badge>
                <span class="text-xs text-slate-500">
                    {{ $createdAt ? $createdAt->format('Y-m-d H:i') : '' }} - {{ $userName }}
                </span>
            </div>

            @if ($newValues)
                <pre class="mt-3 overflow-x-auto rounded-md border border-slate-200 bg-slate-50 p-3 text-xs leading-5 text-slate-700">{{ json_encode($newValues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            @endif
        </div>
    @empty
        <x-ui.empty-state :title="$emptyTitle" />
    @endforelse
</div>
