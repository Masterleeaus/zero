<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    use \App\Traits\BelongsToStore;

    use HasFactory;

    protected $fillable = [
        'store_id', 'order_id', 'return_number', 'total_refund_amount', 'status', 'reason', 'notes',
    ];

    public static function generateNumber(): string
    {
        do {
            $number = 'RET-' . strtoupper(str()->random(8));
        } while (static::where('return_number', $number)->exists());
        
        return $number;
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(SaleReturnItem::class);
    }
}
