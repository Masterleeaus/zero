<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use \App\Traits\BelongsToStore;

    use HasFactory;

    protected $fillable = [
        'order_id', 'courier_id', 'tracking_number', 'status', 
        'shipping_cost', 'cash_to_collect', 'shipped_at', 'delivered_at'
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }
}
