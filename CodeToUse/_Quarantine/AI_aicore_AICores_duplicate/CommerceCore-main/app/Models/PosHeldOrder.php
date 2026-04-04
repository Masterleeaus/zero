<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToStore;

class PosHeldOrder extends Model
{
    use BelongsToStore;

    protected $fillable = [
        'store_id',
        'user_id',
        'reference',
        'cart_data',
        'customer_data',
        'subtotal',
        'total',
    ];

    protected $casts = [
        'cart_data'     => 'array',
        'customer_data' => 'array',
        'subtotal'      => 'decimal:2',
        'total'         => 'decimal:2',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
