<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use \App\Traits\BelongsToStore;

    use HasFactory;

    protected $fillable = [
        'store_id', 'supplier_id', 'purchase_number', 'supplier_name', 'supplier_email',
        'total_amount', 'status', 'payment_status', 'received_at', 'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'received_at' => 'datetime',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public static function generateNumber(): string
    {
        $prefix = 'PO-' . date('Ymd') . '-';
        $last = static::where('purchase_number', 'like', $prefix . '%')->latest('id')->first();
        $seq = $last ? ((int) substr($last->purchase_number, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
