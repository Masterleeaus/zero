<?php

namespace App\Traits;

use App\Models\Scopes\StoreScope;

trait BelongsToStore
{
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new StoreScope);

        static::creating(function ($model) {
            // Automatically assign store_id when creating a model
            if (app()->has('current_tenant_id') && !$model->store_id) {
                $model->store_id = app('current_tenant_id');
            }
        });
    }

    /**
     * Relationship to the Store model.
     */
    public function store()
    {
        return $this->belongsTo(\App\Models\Store::class);
    }
}
