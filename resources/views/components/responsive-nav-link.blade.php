@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-s-4 border-cyan-600 bg-cyan-50 py-2 pe-4 ps-3 text-start text-base font-semibold text-cyan-800 transition duration-150 ease-in-out focus:outline-none focus:bg-cyan-100'
            : 'block w-full border-s-4 border-transparent py-2 pe-4 ps-3 text-start text-base font-medium text-slate-600 transition duration-150 ease-in-out hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:bg-slate-50';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
