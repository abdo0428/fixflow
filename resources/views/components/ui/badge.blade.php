@props([
    'status' => null,
    'variant' => null,
])

@php
    $statusKey = $status ? str_replace('-', '_', strtolower((string) $status)) : null;

    $variant = $variant ?: match ($statusKey) {
        'active', 'completed', 'paid' => 'success',
        'new', 'in_progress', 'on_the_way' => 'primary',
        'under_review', 'scheduled', 'issued', 'medium' => 'info',
        'waiting_parts', 'draft', 'low', 'high' => 'warning',
        'cancelled', 'rejected', 'inactive', 'suspended', 'urgent' => 'danger',
        default => 'muted',
    };

    $classes = match ($variant) {
        'primary' => 'ff-badge ff-badge-primary',
        'success' => 'ff-badge ff-badge-success',
        'warning' => 'ff-badge ff-badge-warning',
        'danger' => 'ff-badge ff-badge-danger',
        'info' => 'ff-badge ff-badge-info',
        default => 'ff-badge ff-badge-muted',
    };

    $label = null;

    if ($statusKey) {
        $statusTranslationKey = "ui.statuses.{$statusKey}";
        $priorityTranslationKey = "ui.priorities.{$statusKey}";
        $label = __($statusTranslationKey);

        if ($label === $statusTranslationKey) {
            $label = __($priorityTranslationKey);
        }

        if ($label === $priorityTranslationKey) {
            $label = str_replace('_', ' ', (string) $status);
        }
    }
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if (trim((string) $slot) !== '')
        {{ $slot }}
    @else
        {{ $label }}
    @endif
</span>
