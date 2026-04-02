<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Premises\Premises;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ServicePlan — schedule definition attached to a ServiceAgreement.
 *
 * Sits between Agreement (entitlement) and ServicePlanVisit (occurrence):
 *
 *   Agreement → ServicePlan → ServicePlanVisit → ServiceJob
 *
 * Status: active | paused | completed | cancelled
 * Frequency: daily | weekly | fortnightly | monthly | quarterly | annual | custom
 */
class ServicePlan extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'service_plans';

    protected $fillable = [
        'company_id',
        'created_by',
        'agreement_id',
        'premises_id',
        'title',
        'frequency',
        'visits_per_cycle',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date'       => 'date',
        'end_date'         => 'date',
        'visits_per_cycle' => 'integer',
    ];

    protected $attributes = [
        'status'           => 'active',
        'frequency'        => 'monthly',
        'visits_per_cycle' => 1,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(ServiceAgreement::class, 'agreement_id');
    }

    public function premises(): BelongsTo
    {
        return $this->belongsTo(Premises::class, 'premises_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(ServicePlanVisit::class, 'service_plan_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
