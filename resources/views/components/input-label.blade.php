@props(['value'])

<label {{ $attributes->merge(['class' => 'ff-form-label']) }}>
    {{ $value ?? $slot }}
</label>
