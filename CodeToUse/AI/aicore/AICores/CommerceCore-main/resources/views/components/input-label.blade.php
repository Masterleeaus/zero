@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-surface-700 dark:text-surface-300 mb-1']) }}>
    {{ $value ?? $slot }}
</label>
