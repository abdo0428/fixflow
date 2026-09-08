@props([
    'title' => null,
    'subtitle' => null,
    'columns' => [],
    'empty' => null,
    'footer' => null,
])

<div {{ $attributes->merge(['class' => 'ff-card overflow-hidden']) }}>
    @if ($title || $subtitle)
        <div class="ff-card-header">
            @if ($title)
                <h3 class="ff-section-title">{{ $title }}</h3>
            @endif
            @if ($subtitle)
                <p class="ff-section-subtitle">{{ $subtitle }}</p>
            @endif
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="ff-table">
            @if (count($columns) > 0)
                <thead>
                    <tr>
                        @foreach ($columns as $column)
                            <th>{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
            @endif

            <tbody>
                @if (trim((string) $slot) !== '')
                    {{ $slot }}
                @else
                    <tr>
                        <td colspan="{{ max(1, count($columns)) }}">
                            <x-ui.empty-state :title="$empty" />
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    @if ($footer)
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $footer }}
        </div>
    @endif
</div>
