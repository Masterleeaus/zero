<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StocktakeLine extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'stocktake_id',
        'item_id',
        'expected_qty',
        'counted_qty',
    ];

    protected $casts = [
        'expected_qty' => 'integer',
        'counted_qty'  => 'integer',
    ];

    public function stocktake(): BelongsTo
    {
        return $this->belongsTo(Stocktake::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }
}
