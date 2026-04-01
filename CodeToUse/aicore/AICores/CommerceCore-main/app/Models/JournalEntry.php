<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToStore;

class JournalEntry extends Model
{
    use BelongsToStore;

    protected $fillable = [
        'store_id',
        'reference',
        'date',
        'description',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    public function getTotalDebitAttribute(): float
    {
        return (float) $this->lines->sum('debit');
    }

    public function getTotalCreditAttribute(): float
    {
        return (float) $this->lines->sum('credit');
    }
}
