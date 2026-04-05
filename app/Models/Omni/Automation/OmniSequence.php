<?php

declare(strict_types=1);

namespace App\Models\Omni\Automation;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Omni\OmniAgent;
use App\Models\Omni\OmniCustomer;
use App\Models\Traits\HasOmniTenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * OmniSequence — Multi-step outreach / nurture sequence.
 *
 * A sequence is a time-delayed series of messages or actions delivered to
 * OmniCustomers. Execution is tracked per-customer via OmniSequenceRun.
 *
 * @property int         $id
 * @property string      $uuid
 * @property int         $company_id
 * @property int|null    $agent_id
 * @property string      $name
 * @property string|null $description
 * @property string      $channel_type
 * @property string      $status
 * @property int         $step_count
 * @property array|null  $metadata
 */
class OmniSequence extends Model
{
    use BelongsToCompany;
    use HasOmniTenancy;

    protected $table = 'omni_sequences';

    protected $fillable = [
        'uuid',
        'company_id',
        'agent_id',
        'name',
        'description',
        'channel_type',
        'status',
        'step_count',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(OmniAgent::class, 'agent_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(OmniSequenceStep::class, 'sequence_id')->orderBy('step_order');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(OmniSequenceRun::class, 'sequence_id');
    }

    // ── Named scopes ─────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForChannel(Builder $query, string $channel): Builder
    {
        return $query->where('channel_type', $channel);
    }

    /**
     * Eager-load steps with this sequence (N+1 guard).
     */
    public function scopeWithSteps(Builder $query): Builder
    {
        return $query->with(['steps']);
    }
}
