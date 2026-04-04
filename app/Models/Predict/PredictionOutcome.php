<?php

declare(strict_types=1);

namespace App\Models\Predict;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PredictionOutcome extends Model
{
    use HasFactory;

    protected $table = 'prediction_outcomes';

    protected $fillable = [
        'prediction_id',
        'outcome_occurred',
        'outcome_at',
        'variance_hours',
        'feedback_notes',
        'recorded_by',
    ];

    protected $casts = [
        'outcome_occurred' => 'boolean',
        'outcome_at'       => 'datetime',
        'variance_hours'   => 'float',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function prediction(): BelongsTo
    {
        return $this->belongsTo(Prediction::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
