<?php

declare(strict_types=1);

namespace App\Models\Omni\Automation;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OmniAutomationAction — Individual action step within an OmniAutomation.
 *
 * Action types: send_message | assign_agent | add_tag | start_sequence
 *               fire_webhook | create_crm_note | resolve_conversation
 *
 * @property int         $id
 * @property int         $automation_id
 * @property int         $company_id
 * @property int         $action_order
 * @property string      $action_type
 * @property array|null  $action_config
 * @property array|null  $metadata
 */
class OmniAutomationAction extends Model
{
    use BelongsToCompany;

    protected $table = 'omni_automation_actions';

    protected $fillable = [
        'automation_id',
        'company_id',
        'action_order',
        'action_type',
        'action_config',
        'metadata',
    ];

    protected $casts = [
        'action_config' => 'array',
        'metadata'      => 'array',
    ];

    public function automation(): BelongsTo
    {
        return $this->belongsTo(OmniAutomation::class, 'automation_id');
    }

    // ── Named scopes ─────────────────────────────────────────────────────────

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('action_type', $type);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('action_order');
    }
}
