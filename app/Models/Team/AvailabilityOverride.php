<?php

declare(strict_types=1);

namespace App\Models\Team;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailabilityOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'available',
        'reason',
    ];

    protected $casts = [
        'date'      => 'date',
        'available' => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
