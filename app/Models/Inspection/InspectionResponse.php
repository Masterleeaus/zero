<?php

declare(strict_types=1);

namespace App\Models\Inspection;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Technician response to an InspectionItem within an InspectionInstance.
 *
 * Result values: pass | fail | na | pending
 */
class InspectionResponse extends Model
{
    protected $table = 'inspection_responses';

    protected $fillable = [
        'inspection_instance_id',
        'inspection_item_id',
        'result',
        'numeric_value',
        'text_value',
        'notes',
        'photo_required',
        'signature_captured',
        'responded_by',
        'responded_at',
    ];

    protected $casts = [
        'numeric_value'      => 'decimal:3',
        'photo_required'     => 'boolean',
        'signature_captured' => 'boolean',
        'responded_at'       => 'datetime',
    ];

    protected $attributes = [
        'photo_required'     => false,
        'signature_captured' => false,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function instance(): BelongsTo
    {
        return $this->belongsTo(InspectionInstance::class, 'inspection_instance_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InspectionItem::class, 'inspection_item_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function hasPassed(): bool
    {
        return $this->result === 'pass';
    }

    public function hasFailed(): bool
    {
        return $this->result === 'fail';
    }
}
