@props([
    'title' => null,
    'message' => null,
    'href' => null,
    'action' => null,
])

<div {{ $attributes->merge(['class' => 'ff-empty-state']) }}>
    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-white text-cyan-700 ring-1 ring-slate-200">
        <span class="text-lg font-semibold">+</span>
    </div>

    <h3 class="mt-4 text-sm font-semibold text-slate-950">{{ $title ?? __('ui.empty.default_title') }}</h3>

    @if ($message)
        <p class="mt-2 text-sm leading-6 text-slate-500">{{ $message }}</p>
    @elseif (! $title)
        <p class="mt-2 text-sm leading-6 text-slate-500">{{ __('ui.empty.default_message') }}</p>
    @endif

    @if (trim((string) $slot) !== '')
        <div class="mt-5">
            {{ $slot }}
        </div>
    @elseif ($href && $action)
        <div class="mt-5">
            <x-ui.button :href="$href" size="sm">{{ $action }}</x-ui.button>
        </div>
    @endif
</div>
