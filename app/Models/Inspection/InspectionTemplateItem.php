<?php

declare(strict_types=1);

namespace App\Models\Inspection;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Line item belonging to an InspectionTemplate.
 *
 * Response types: pass_fail | numeric | text | photo_required | signature_required | checkbox
 */
class InspectionTemplateItem extends Model
{
    protected $table = 'inspection_template_items';

    protected $fillable = [
        'inspection_template_id',
        'label',
        'response_type',
        'is_required',
        'sort_order',
        'instructions',
    ];

    protected $casts = [
        'is_required'  => 'boolean',
        'sort_order'   => 'integer',
    ];

    protected $attributes = [
        'response_type' => 'pass_fail',
        'is_required'   => true,
        'sort_order'    => 0,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function template(): BelongsTo
    {
        return $this->belongsTo(InspectionTemplate::class, 'inspection_template_id');
    }
}
