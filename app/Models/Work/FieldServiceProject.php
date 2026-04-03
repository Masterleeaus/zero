<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Crm\Customer;
use App\Models\Premises\Premises;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FieldServiceProject extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;
    use SoftDeletes;

    protected $table = 'field_service_projects';

    protected $fillable = [
        'company_id',
        'created_by',
        'name',
        'reference',
        'description',
        'status',
        'customer_id',
        'premises_id',
        'team_id',
        'assigned_user_id',
        'planned_start',
        'planned_end',
        'actual_start',
        'actual_end',
        'estimated_hours',
        'notes',
    ];

    protected $casts = [
        'planned_start'   => 'date',
        'planned_end'     => 'date',
        'actual_start'    => 'date',
        'actual_end'      => 'date',
        'estimated_hours' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function premises(): BelongsTo
    {
        return $this->belongsTo(Premises::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function serviceJobs(): HasMany
    {
        return $this->hasMany(ServiceJob::class, 'project_id');
    }

    public function serviceVisits(): HasMany
    {
        return $this->hasMany(ServicePlanVisit::class, 'project_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForPremises(Builder $query, int $premisesId): Builder
    {
        return $query->where('premises_id', $premisesId);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function executionProgress(): array
    {
        $jobs      = $this->serviceJobs;
        $total     = $jobs->count();
        $completed = $jobs->whereIn('status', ['completed', 'closed'])->count();

        return [
            'total_jobs'       => $total,
            'completed_jobs'   => $completed,
            'pending_jobs'     => $total - $completed,
            'percent_complete' => $total > 0 ? round(($completed / $total) * 100) : 0,
        ];
    }

    public function coverageSummary(): array
    {
        return [
            'project_id'    => $this->id,
            'name'          => $this->name,
            'status'        => $this->status,
            'planned_start' => $this->planned_start?->toDateString(),
            'planned_end'   => $this->planned_end?->toDateString(),
            'job_count'     => $this->serviceJobs()->count(),
            'visit_count'   => $this->serviceVisits()->count(),
        ];
    }
}
