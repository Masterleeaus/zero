<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsActivity;

class Coupon extends Model
{
    use \App\Traits\BelongsToStore;

    use LogsActivity;
    protected $fillable = [
        'store_id', 'code', 'type', 'value', 'min_spend', 
        'expires_at', 'usage_limit', 'used_count', 'is_active'
    ];
}
