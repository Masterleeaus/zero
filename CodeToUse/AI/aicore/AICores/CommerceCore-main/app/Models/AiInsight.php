<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToStore;

class AiInsight extends Model
{
    use BelongsToStore;

    protected $fillable = [
        'store_id', 'engine', 'type', 'title', 'description',
        'recommendation', 'data', 'status', 'confidence',
    ];

    protected $casts = [
        'data' => 'array',
        'confidence' => 'decimal:2',
    ];

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeByEngine($query, string $engine)
    {
        return $query->where('engine', $engine);
    }

    public function dismiss()
    {
        $this->update(['status' => 'dismissed']);
    }

    public function markActedOn()
    {
        $this->update(['status' => 'acted_on']);
    }
}
