<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BuilderPage extends Model
{
    use \App\Traits\BelongsToStore;

    protected $fillable = ['store_id', 'page_name', 'slug', 'is_homepage', 'is_published', 'sort_order'];

    protected $casts = [
        'is_homepage' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function sections()
    {
        return $this->hasMany(BuilderSection::class, 'page_id')->orderBy('position');
    }

    public function activeSections()
    {
        return $this->hasMany(BuilderSection::class, 'page_id')
            ->where('is_active', true)
            ->orderBy('position');
    }
}
