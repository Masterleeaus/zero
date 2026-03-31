<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\User;
use App\Models\Team\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceJob extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $fillable = [
        'company_id',
        'created_by',
        'team_id',
        'site_id',
        'customer_id',
        'quote_id',
        'agreement_id',
        'assigned_user_id',
        'stage_id',
        'job_type_id',
        'template_id',
        'title',
        'status',
        'priority',
        'sequence',
        'territory_id',
        'branch_id',
        'district_id',
        'scheduled_at',
        'scheduled_date_start',
        'scheduled_duration',
        'scheduled_date_end',
        'date_start',
        'date_end',
        'notes',
        'todo',
        'resolution',
        'signed_by',
        'signed_on',
        'require_signature',
    ];

    protected $casts = [
        'scheduled_at'         => 'datetime',
        'scheduled_date_start' => 'datetime',
        'scheduled_date_end'   => 'datetime',
        'date_start'           => 'datetime',
        'date_end'             => 'datetime',
        'signed_on'            => 'datetime',
        'require_signature'    => 'boolean',
        'scheduled_duration'   => 'float',
        'sequence'             => 'integer',
    ];

    protected $attributes = [
        'status'             => 'scheduled',
        'priority'           => 'normal',
        'sequence'           => 10,
        'require_signature'  => false,
        'scheduled_duration' => 0,
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(ServiceAgreement::class, 'agreement_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Crm\Customer::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Money\Quote::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(JobStage::class, 'stage_id');
    }

    public function jobType(): BelongsTo
    {
        return $this->belongsTo(JobType::class, 'job_type_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(JobTemplate::class, 'template_id');
    }

    public function territory(): BelongsTo
    {
        return $this->belongsTo(Territory::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class, 'service_job_id');
    }

    public function workers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'service_job_workers', 'service_job_id', 'user_id');
    }

    // ── Computed ─────────────────────────────────────────────────────────────

    public function getDurationAttribute(): float
    {
        if ($this->date_start && $this->date_end) {
            return round($this->date_start->diffInMinutes($this->date_end) / 60, 2);
        }

        return (float) $this->scheduled_duration;
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull($query->qualifyColumn('assigned_user_id'));
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->whereIn('status', ['completed', 'cancelled']);
    }

    public function scopeForTerritory(Builder $query, int $territoryId): Builder
    {
        return $query->where('territory_id', $territoryId);
    }

    public function scopeNeedsSignature(Builder $query): Builder
    {
        return $query->where('require_signature', true)->whereNull('signed_on');
    }
}
