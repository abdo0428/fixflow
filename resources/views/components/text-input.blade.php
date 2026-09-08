@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'ff-form-input']) }}>
