<button {{ $attributes->merge(['type' => 'submit', 'class' => 'ff-button ff-button-danger ff-button-sm uppercase tracking-wider']) }}>
    {{ $slot }}
</button>
