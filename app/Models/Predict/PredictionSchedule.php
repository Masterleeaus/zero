<?php

declare(strict_types=1);

namespace App\Models\Predict;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PredictionSchedule extends Model
{
    use BelongsToCompany;
    use HasFactory;

    protected $table = 'prediction_schedules';

    protected $fillable = [
        'company_id',
        'prediction_type',
        'frequency_hours',
        'last_run_at',
        'next_run_at',
        'is_active',
        'config',
    ];

    protected $casts = [
        'frequency_hours' => 'integer',
        'last_run_at'     => 'datetime',
        'next_run_at'     => 'datetime',
        'is_active'       => 'boolean',
        'config'          => 'array',
    ];

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDue(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true)
            ->where(static function ($q) {
                $q->whereNull('next_run_at')->orWhere('next_run_at', '<=', now());
            });
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function advanceNextRun(): void
    {
        $this->last_run_at = now();
        $this->next_run_at = now()->addHours($this->frequency_hours);
        $this->save();
    }

    public function getProvider(): string
    {
        return $this->config['provider'] ?? 'anthropic';
    }

    public function getModelId(): string
    {
        return $this->config['model_id'] ?? 'claude-3-haiku-20240307';
    }
}
