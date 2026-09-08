@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'ff-alert-success']) }}>
        {{ $status }}
    </div>
@endif
