<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbandonedCart extends Model
{
    use \App\Traits\BelongsToStore;

    protected $fillable = ['email', 'phone', 'cart_data', 'last_active_at', 'is_recovered'];
    protected $casts = ['cart_data' => 'array', 'last_active_at' => 'datetime'];
}
