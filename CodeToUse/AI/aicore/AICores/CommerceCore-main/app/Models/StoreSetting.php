<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreSetting extends Model
{
    protected $fillable = ['store_id', 'theme_id', 'settings_json'];

    protected $casts = [
        'settings_json' => 'array',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings_json, $key, $default);
    }
}
