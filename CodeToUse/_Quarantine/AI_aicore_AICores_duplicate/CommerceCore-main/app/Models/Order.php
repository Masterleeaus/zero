<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\LogsActivity;

class Order extends Model
{
    use \App\Traits\BelongsToStore, HasFactory;

    use LogsActivity;
    protected $fillable = [
        'store_id', 'user_id', 'customer_id', 'order_number', 'status', 'subtotal', 'tax',
        'total_price', 'customer_name', 'customer_email', 'phone', 'address',
        'shipping_address', 'notes', 'lifecycle_status', 'assigned_agent_id', 'locked_at',
        'requires_confirmation', 'is_confirmed', 'checkout_notes', 'fraud_score', 'risk_explanation',
        'is_subscription', 'parent_subscription_id', 'next_billing_at'
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total_price' => 'decimal:2',
            'shipping_address' => 'array',
            'locked_at' => 'datetime',
            'is_subscription' => 'boolean',
            'next_billing_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function parentSubscription(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'parent_subscription_id');
    }

    public function recurringOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'parent_subscription_id');
    }

    public function shipment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    public static function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(uniqid());
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'paid' => 'blue',
            'shipped' => 'indigo',
            'delivered' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }
}
