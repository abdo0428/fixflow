@props([
    'title' => null,
    'subtitle' => null,
    'padding' => 'ff-card-body',
])

<section {{ $attributes->merge(['class' => 'ff-card overflow-hidden']) }}>
    @if ($title || $subtitle || isset($header))
        <div class="ff-card-header">
            @isset($header)
                {{ $header }}
            @else
                <h3 class="ff-section-title">{{ $title }}</h3>
                @if ($subtitle)
                    <p class="ff-section-subtitle">{{ $subtitle }}</p>
                @endif
            @endisset
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>
</section>
