<?php

declare(strict_types=1);

namespace App\Models\Work;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Technician response to a ChecklistItem within a ChecklistRun.
 *
 * Result values: pass | fail | na
 */
class ChecklistResponse extends Model
{
    protected $table = 'checklist_responses';

    protected $fillable = [
        'checklist_run_id',
        'checklist_item_id',
        'result',
        'is_checked',
        'numeric_value',
        'text_value',
        'notes',
        'photo_required',
        'photo_path',
        'signature_captured',
        'responded_by',
        'responded_at',
    ];

    protected $casts = [
        'is_checked'         => 'boolean',
        'numeric_value'      => 'decimal:3',
        'photo_required'     => 'boolean',
        'signature_captured' => 'boolean',
        'responded_at'       => 'datetime',
    ];

    protected $attributes = [
        'is_checked'         => false,
        'photo_required'     => false,
        'signature_captured' => false,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function run(): BelongsTo
    {
        return $this->belongsTo(ChecklistRun::class, 'checklist_run_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ChecklistItem::class, 'checklist_item_id');
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
