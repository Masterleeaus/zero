<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\Setting;
use App\Models\SettingTwo;

/**
 * AdminSettingsService
 *
 * Reads and writes system settings stored in the `settings` and `settings_two`
 * tables, which are Titan Zero's canonical configuration stores.
 *
 * Both tables are single-row models where each column is an individual setting
 * attribute. Use the `setting()` / `setting($data)->save()` pattern to read/write.
 */
class AdminSettingsService
{
    /**
     * Retrieve a setting attribute from the primary settings table.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Setting::getCache()?->getAttribute($key) ?? $default;
    }

    /**
     * Persist one or more attributes to the primary settings table.
     *
     * @param  array<string, mixed>  $data
     */
    public function bulkUpdate(array $data): void
    {
        $setting = Setting::first();

        if ($setting === null) {
            return;
        }

        $setting->fill($data)->save();
        Setting::forgetCache();
    }

    /**
     * Return the entire settings instance as an attribute array.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return Setting::getCache()?->getAttributes() ?? [];
    }

    /**
     * Read a setting attribute from the `settings_two` (extended) table.
     */
    public function getExtended(string $key, mixed $default = null): mixed
    {
        return SettingTwo::getCache()?->getAttribute($key) ?? $default;
    }

    /**
     * Persist one or more attributes to the `settings_two` (extended) table.
     *
     * @param  array<string, mixed>  $data
     */
    public function bulkUpdateExtended(array $data): void
    {
        $setting = SettingTwo::first();

        if ($setting === null) {
            return;
        }

        $setting->fill($data)->save();
        SettingTwo::forgetCache();
    }
}
