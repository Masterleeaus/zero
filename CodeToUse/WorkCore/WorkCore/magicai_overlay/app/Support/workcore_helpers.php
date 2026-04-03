<?php

use App\Services\VerticalLanguageResolver;

if (! function_exists('workcore_label')) {
    function workcore_label(string $key, ?string $fallback = null): string
    {
        if (! function_exists('app')) {
            return $fallback ?? $key;
        }

        return app(VerticalLanguageResolver::class)->label($key, $fallback);
    }
}

if (! function_exists('worksuite_label')) {
    function worksuite_label(string $key, ?string $fallback = null): string
    {
        return workcore_label($key, $fallback);
    }
}
