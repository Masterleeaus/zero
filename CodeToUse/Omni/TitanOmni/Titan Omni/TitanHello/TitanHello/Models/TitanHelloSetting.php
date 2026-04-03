<?php

namespace Extensions\TitanHello\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Extension-local key/value store.
 *
 * We keep settings storage inside the extension so it works across MagicAI
 * builds where the core settings helper may differ.
 */
class TitanHelloSetting extends Model
{
    protected $table = 'titan_hello_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    public $timestamps = true;

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Convenience: fetch a key from storage.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $row = static::query()->where('key', $key)->first();
        return $row?->value ?? $default;
    }

    /**
     * Convenience: set a key.
     */
    public static function setValue(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
