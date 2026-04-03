<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsActivity;

class ProductVariant extends Model
{
    use \App\Traits\BelongsToStore;

    use HasFactory, LogsActivity;

    protected $fillable = ['product_id', 'name', 'sku', 'price_adjustment', 'stock', 'image'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
