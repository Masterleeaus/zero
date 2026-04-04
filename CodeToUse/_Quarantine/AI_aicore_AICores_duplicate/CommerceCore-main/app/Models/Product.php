<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class Product extends Model
{
    use \App\Traits\BelongsToStore;

    use LogsActivity;
    protected $fillable = [
        'store_id', 'category_id', 'name', 'type', 'slug', 'description', 'price',
        'compare_price', 'sku', 'stock', 'alert_quantity', 'image', 'gallery', 'status', 'featured',
        'pre_order', 'expected_date', 'allow_subscription', 'subscription_interval', 'subscription_discount_percentage'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'gallery' => 'array',
        'featured' => 'boolean',
        'allow_subscription' => 'boolean',
        'subscription_discount_percentage' => 'decimal:2',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function comboItems()
    {
        return $this->belongsToMany(Product::class, 'combo_items', 'combo_product_id', 'single_product_id')
                    ->withPivot('quantity');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasDiscount(): bool
    {
        return $this->compare_price && $this->compare_price > $this->price;
    }

    public function discountPercentage(): int
    {
        if (!$this->hasDiscount()) return 0;
        return (int) round((($this->compare_price - $this->price) / $this->compare_price) * 100);
    }

    public function inStock(): bool
    {
        return $this->stock > 0;
    }
}
