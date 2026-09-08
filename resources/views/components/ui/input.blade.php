@props([
    'disabled' => false,
    'type' => 'text',
    'error' => false,
])

@php
    $classes = $error
        ? 'ff-form-input border-red-300 focus:border-red-500 focus:ring-red-500'
        : 'ff-form-input';
@endphp

<input type="{{ $type }}" @disabled($disabled) {{ $attributes->merge(['class' => $classes]) }}>
