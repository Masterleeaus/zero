<?php

declare(strict_types=1);

namespace App\Models\Predict;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prediction extends Model
{
    use BelongsToCompany;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'predictions';

    protected $fillable = [
        'company_id',
        'prediction_type',
        'subject_type',
        'subject_id',
        'confidence_score',
        'predicted_at',
        'generated_at',
        'expires_at',
        'status',
        'recommended_action',
        'explanation_trace',
        'model_provider',
        'model_id',
        'dismissed_by',
        'dismissed_at',
    ];

    protected $casts = [
        'confidence_score'  => 'decimal:4',
        'predicted_at'      => 'datetime',
        'generated_at'      => 'datetime',
        'expires_at'        => 'datetime',
        'dismissed_at'      => 'datetime',
        'explanation_trace' => 'array',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function signals(): HasMany
    {
        return $this->hasMany(PredictionSignal::class);
    }

    public function outcome(): HasOne
    {
        return $this->hasOne(PredictionOutcome::class);
    }

    public function dismissedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'dismissed_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeTriggered(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'triggered');
    }

    public function scopeDismissed(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'dismissed');
    }

    public function scopeExpired(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'expired');
    }

    public function scopeOfType(\Illuminate\Database\Eloquent\Builder $query, string $type): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('prediction_type', $type);
    }

    public function scopeHighConfidence(\Illuminate\Database\Eloquent\Builder $query, float $threshold = 0.85): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('confidence_score', '>=', $threshold);
    }

    public function scopeNotExpired(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where(static function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isHighConfidence(float $threshold = 0.85): bool
    {
        return (float) $this->confidence_score >= $threshold;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
