<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\LogsActivity;

class ProductBatch extends Model
{
    use \App\Traits\BelongsToStore;

    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = ['product_id', 'product_variant_id', 'batch_number', 'mfg_date', 'expiry_date', 'cost_price', 'quantity'];

    protected $casts = [
        'mfg_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
