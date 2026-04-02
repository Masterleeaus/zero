<?php

declare(strict_types=1);

namespace App\Models\Work;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Line item belonging to a ChecklistTemplate.
 *
 * Response types: pass_fail | checkbox | numeric | text |
 *                 photo_required | signature_required | notes
 */
class ChecklistItem extends Model
{
    protected $table = 'checklist_items';

    protected $fillable = [
        'checklist_template_id',
        'label',
        'response_type',
        'is_required',
        'sort_order',
        'guidance',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'sort_order'  => 'integer',
    ];

    protected $attributes = [
        'response_type' => 'pass_fail',
        'is_required'   => true,
        'sort_order'    => 0,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function template(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class, 'checklist_template_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(ChecklistResponse::class, 'checklist_item_id');
    }
}
