@props([
    'title',
    'value',
    'helper' => null,
    'tone' => 'primary',
])

@php
    $toneClasses = match ($tone) {
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-red-500',
        'info' => 'bg-blue-500',
        default => 'bg-cyan-600',
    };
@endphp

<x-ui.card {{ $attributes->merge(['class' => 'relative']) }}>
    <div class="absolute inset-x-0 top-0 h-1 {{ $toneClasses }}"></div>
    <div class="text-sm font-medium text-slate-500">{{ $title }}</div>
    <div class="mt-2 text-3xl font-semibold tracking-normal text-slate-950">{{ $value }}</div>
    @if ($helper)
        <div class="mt-2 text-sm text-slate-500">{{ $helper }}</div>
    @endif
</x-ui.card>
