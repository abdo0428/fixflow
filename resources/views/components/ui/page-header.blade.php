@props([
    'title',
    'subtitle' => null,
    'eyebrow' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between']) }}>
    <div>
        @if ($eyebrow)
            <div class="text-xs font-semibold uppercase tracking-wider text-cyan-700">{{ $eyebrow }}</div>
        @endif

        <h1 class="text-xl font-semibold leading-7 tracking-normal text-slate-950">{{ $title }}</h1>

        @if ($subtitle)
            <p class="mt-1 text-sm leading-6 text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
