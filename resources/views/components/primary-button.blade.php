<button {{ $attributes->merge(['type' => 'submit', 'class' => 'ff-button ff-button-primary ff-button-sm uppercase tracking-wider']) }}>
    {{ $slot }}
</button>
