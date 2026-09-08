@props([
    'compact' => false,
])

@php($switcherId = 'locale-switcher-'.uniqid())

<form method="POST" action="{{ route('locale.update') }}" {{ $attributes->merge(['class' => 'inline-flex items-center']) }}>
    @csrf

    <label for="{{ $switcherId }}" class="sr-only">
        {{ __('ui.actions.change_language') }}
    </label>
    <select
        id="{{ $switcherId }}"
        name="locale"
        class="h-9 rounded-md border-slate-300 bg-white px-2 py-1 text-sm font-medium text-slate-700 shadow-sm focus:border-cyan-700 focus:ring-cyan-700"
        onchange="this.form.submit()"
    >
        @foreach (config('app.supported_locales', ['ar', 'en', 'tr']) as $locale)
            <option value="{{ $locale }}" @selected(app()->getLocale() === $locale)>
                {{ __('ui.locales.'.$locale) }}
            </option>
        @endforeach
    </select>
</form>
