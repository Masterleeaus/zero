<?php

use App\Services\VerticalLanguageResolver;

if (! function_exists('workcore_label')) {
    /**
     * Resolve a vertical-specific display label for the given key.
     * Example: workcore_label('sites') → 'Jobs' (cleaning vertical)
     */
    function workcore_label(string $key, ?string $default = null): string
    {
        return app(VerticalLanguageResolver::class)->label($key, $default);
    }
}

if (! function_exists('workcore_feature')) {
    /**
     * Check if a WorkCore feature flag is enabled.
     * Example: workcore_feature('teamchat') → false
     */
    function workcore_feature(string $key): bool
    {
        return app(VerticalLanguageResolver::class)->feature($key);
    }
}

if (! function_exists('workcore_vertical')) {
    /**
     * Get the active vertical name.
     */
    function workcore_vertical(): string
    {
        return app(VerticalLanguageResolver::class)->vertical();
    }
}
