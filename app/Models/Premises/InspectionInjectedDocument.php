<?php

declare(strict_types=1);

namespace App\Models\Premises;

use App\Models\Inspection\InspectionInstance;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tracks a document that has been injected into an inspection instance.
 *
 * injection_source values: rule | ai_relevance | manual
 */
class InspectionInjectedDocument extends Model
{
    protected $table = 'inspection_injected_documents';

    protected $fillable = [
        'inspection_instance_id',
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

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(InspectionInstance::class, 'inspection_instance_id');
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
