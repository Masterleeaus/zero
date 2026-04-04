<?php

declare(strict_types=1);

namespace App\Models\Premises;

use App\Models\User;
use App\Models\Work\ServiceJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tracks a document that has been injected into a service job.
 *
 * injection_source values: rule | ai_relevance | manual
 */
class JobInjectedDocument extends Model
{
    protected $table = 'job_injected_documents';

    protected $fillable = [
        'job_id',
        'document_id',
        'injection_source',
        'relevance_score',
        'injected_at',
        'acknowledged_by',
        'acknowledged_at',
        'is_mandatory',
    ];

    protected $casts = [
        'injected_at'     => 'datetime',
        'acknowledged_at' => 'datetime',
        'relevance_score' => 'decimal:4',
        'is_mandatory'    => 'boolean',
    ];

    protected $attributes = [
        'injection_source' => 'rule',
        'is_mandatory'     => false,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function job(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class, 'job_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(FacilityDocument::class, 'document_id');
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isAcknowledged(): bool
    {
        return $this->acknowledged_at !== null;
    }
}
