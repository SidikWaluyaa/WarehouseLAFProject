@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-xs tracking-wider uppercase text-on-surface-variant mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>
