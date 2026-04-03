<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToStore;

class SystemAlert extends Model
{
    use BelongsToStore;

    protected $fillable = [
        'store_id', 'type', 'severity', 'title', 'message',
        'suggested_action', 'status', 'metadata', 'resolved_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function resolve()
    {
        $this->update(['status' => 'resolved', 'resolved_at' => now()]);
    }
}
