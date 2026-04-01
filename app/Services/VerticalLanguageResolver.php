<?php

namespace App\Services;

class VerticalLanguageResolver
{
    protected string $vertical;
    protected array $vocabulary;

    public function __construct()
    {
        $this->vertical = config('workcore.vertical', 'cleaning');
        $this->vocabulary = config('verticals.' . $this->vertical, config('verticals.cleaning', []));
    }

    /**
     * Resolve a display label for the given internal key.
     * Falls back to $default if key not found in the vertical map.
     */
    public function label(string $key, ?string $default = null): string
    {
        return $this->vocabulary[$key] ?? $default ?? ucfirst(str_replace('_', ' ', $key));
    }

    /**
     * Check if a feature flag is enabled in workcore config.
     */
    public function feature(string $key): bool
    {
        return (bool) config('workcore.features.' . $key, false);
    }

    /**
     * Get the active vertical name.
     */
    public function vertical(): string
    {
        return $this->vertical;
    }
}
