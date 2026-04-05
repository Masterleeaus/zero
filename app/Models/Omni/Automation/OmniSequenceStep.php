<?php

declare(strict_types=1);

namespace App\Models\Omni\Automation;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OmniSequenceStep — Individual step within an OmniSequence.
 *
 * Steps are ordered and may carry delay, content, conditions, or action config.
 *
 * @property int         $id
 * @property int         $sequence_id
 * @property int         $company_id
 * @property int         $step_order
 * @property string      $step_type
 * @property string|null $content
 * @property string      $content_type
 * @property int         $delay_minutes
 * @property array|null  $conditions
 * @property array|null  $metadata
 */
class OmniSequenceStep extends Model
{
    use BelongsToCompany;

    protected $table = 'omni_sequence_steps';

    protected $fillable = [
        'sequence_id',
        'company_id',
        'step_order',
        'step_type',
        'content',
        'content_type',
        'delay_minutes',
        'conditions',
        'metadata',
    ];

    protected $casts = [
        'conditions' => 'array',
        'metadata'   => 'array',
    ];

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(OmniSequence::class, 'sequence_id');
    }

    // ── Named scopes ─────────────────────────────────────────────────────────

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('step_type', $type);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('step_order');
    }
}
