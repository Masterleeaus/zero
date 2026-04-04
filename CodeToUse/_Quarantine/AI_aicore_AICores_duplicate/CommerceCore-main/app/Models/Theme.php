<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    protected $fillable = ['name', 'config_json', 'is_active'];

    protected $casts = [
        'config_json' => 'array',
        'is_active' => 'boolean',
    ];

    public function storeSettings(): HasMany
    {
        return $this->hasMany(StoreSetting::class);
    }
}
