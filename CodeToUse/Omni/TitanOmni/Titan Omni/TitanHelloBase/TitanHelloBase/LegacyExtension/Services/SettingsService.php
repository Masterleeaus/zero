<?php

namespace Extensions\TitanHello\Services;

use Extensions\TitanHello\Models\TitanHelloSetting;

class SettingsService
{
    /**
     * Return merged settings: stored overrides win over config defaults.
     */
    public function all(): array
    {
        $defaults = config('titan-hello', []);
        $stored = TitanHelloSetting::query()
            ->pluck('value', 'key')
            ->toArray();

        // Flatten defaults to dot-keys for the form.
        $flatDefaults = $this->flatten($defaults);

        return array_merge($flatDefaults, $stored);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return TitanHelloSetting::getValue($key, $default);
    }

    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            TitanHelloSetting::setValue($key, $value);
        }
    }

    private function flatten(array $arr, string $prefix = ''): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            $key = $prefix === '' ? (string) $k : $prefix . '.' . $k;
            if (is_array($v)) {
                $out = array_merge($out, $this->flatten($v, $key));
            } else {
                $out[$key] = $v;
            }
        }
        return $out;
    }
}
