<?php

declare(strict_types=1);

namespace App\Models\Omni\Automation;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Omni\OmniAgent;
use App\Models\Omni\OmniConversation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OmniOverlayBinding — Per-company surface overlay / widget configuration.
 *
 * Binds an OmniAgent (and optionally a conversation context) to a named
 * surface key (e.g., web embed, portal, mobile app). Enables per-surface
 * configuration of the chat widget without duplicating agent definitions.
 *
 * @property int         $id
 * @property int         $company_id
 * @property int|null    $agent_id
 * @property int|null    $conversation_id
 * @property string      $surface
 * @property string      $binding_key
 * @property array|null  $config
 * @property bool        $is_active
 * @property \Carbon\Carbon|null $bound_at
 * @property \Carbon\Carbon|null $unbound_at
 * @property array|null  $metadata
 */
class OmniOverlayBinding extends Model
{
    use BelongsToCompany;

    protected $table = 'omni_overlay_bindings';

    protected $fillable = [
        'company_id',
        'agent_id',
        'conversation_id',
        'surface',
        'binding_key',
        'config',
        'is_active',
        'bound_at',
        'unbound_at',
        'metadata',
    ];

    protected $casts = [
        'config'     => 'array',
        'is_active'  => 'boolean',
        'bound_at'   => 'datetime',
        'unbound_at' => 'datetime',
        'metadata'   => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function (self $model) {
            if (empty($model->bound_at)) {
                $model->bound_at = now();
            }
        });
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(OmniAgent::class, 'agent_id');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(OmniConversation::class, 'conversation_id');
    }

    // ── Named scopes ─────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForSurface(Builder $query, string $surface): Builder
    {
        return $query->where('surface', $surface);
    }

    public function scopeByKey(Builder $query, string $key): Builder
    {
        return $query->where('binding_key', $key);
    }
}
