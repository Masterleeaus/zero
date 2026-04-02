<?php

declare(strict_types=1);

namespace App\Models\Inspection;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Line item within an InspectionInstance.
 *
 * Copied from the template items at instance creation, so the template
 * can change without affecting historical instances.
 */
class InspectionItem extends Model
{
    protected $table = 'inspection_items';

    protected $fillable = [
        'inspection_instance_id',
        'template_item_id',
        'label',
        'response_type',
        'is_required',
        'sort_order',
        'instructions',
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

    public function instance(): BelongsTo
    {
        return $this->belongsTo(InspectionInstance::class, 'inspection_instance_id');
    }

    public function templateItem(): BelongsTo
    {
        return $this->belongsTo(InspectionTemplateItem::class, 'template_item_id');
    }

    public function response(): HasOne
    {
        return $this->hasOne(InspectionResponse::class, 'inspection_item_id');
    }
}
