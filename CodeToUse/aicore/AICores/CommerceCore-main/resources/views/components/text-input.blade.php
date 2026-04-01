@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-surface-300 dark:border-surface-600 dark:bg-surface-800 dark:text-surface-100 focus:border-primary-500 dark:focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-500 rounded-md shadow-sm']) }}>
