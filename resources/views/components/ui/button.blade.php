@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
])

@php
    $variantClasses = match ($variant) {
        'secondary' => 'ff-button-secondary',
        'success' => 'ff-button-success',
        'warning' => 'ff-button-warning',
        'danger' => 'ff-button-danger',
        'ghost' => 'ff-button-ghost',
        default => 'ff-button-primary',
    };

    $sizeClasses = match ($size) {
        'sm' => 'ff-button-sm',
        'lg' => 'ff-button-lg',
        default => '',
    };

    $classes = trim("ff-button {$variantClasses} {$sizeClasses}");
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
