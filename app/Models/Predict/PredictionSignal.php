<?php

declare(strict_types=1);

namespace App\Models\Predict;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PredictionSignal extends Model
{
    use HasFactory;

    protected $table = 'prediction_signals';

    protected $fillable = [
        'prediction_id',
        'signal_type',
        'signal_source_type',
        'signal_source_id',
        'signal_value',
        'weight',
        'recorded_at',
    ];

    protected $casts = [
        'signal_value' => 'array',
        'weight'       => 'decimal:4',
        'recorded_at'  => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function prediction(): BelongsTo
    {
        return $this->belongsTo(Prediction::class);
    }

    public function source(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo('signal_source');
    }
}
