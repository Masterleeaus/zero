<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $fillable = [
        'company_id',
        'created_by',
        'team_id',
        'service_area_id',
        'name',
        'reference',
        'address',
        'direction',
        'status',
        'start_date',
        'deadline',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline'   => 'date',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    public function serviceArea(): BelongsTo
    {
        return $this->belongsTo(ServiceArea::class, 'service_area_id');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(ServiceJob::class);
    }

    public function territory(): BelongsTo
    {
        return $this->belongsTo(Territory::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Site::class, 'parent_id');
    }

    public function workers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'site_workers', 'site_id', 'user_id')
            ->withPivot('sequence')
            ->orderByPivot('sequence');
    }

    // ── Computed ─────────────────────────────────────────────────────────────

    public function getCompleteNameAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->complete_name . ' / ' . $this->name;
        }

        return $this->name;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeRootSites(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeForTerritory(Builder $query, int $territoryId): Builder
    {
        return $query->where('territory_id', $territoryId);
    }

    /**
     * Copy the site's default workers onto a ServiceJob.
     *
     * Syncs the site_workers pivot entries to the service_job_workers
     * pivot so that new jobs inherit the site's regular workforce.
     */
    public function inheritWorkersToJob(ServiceJob $job): void
    {
        $workerIds = $this->workers()->pluck('users.id')->all();

        if (! empty($workerIds)) {
            $job->workers()->syncWithoutDetaching($workerIds);
        }
    }

    /**
     * Query helper: returns all pending (todo) activities across all jobs at this site.
     *
     * Ordered by follow_up_at ASC (nulls last), then sequence ASC.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Work\JobActivity>
     */
    public function pendingActivities(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Work\JobActivity::query()
            ->where('company_id', $this->company_id)
            ->where('state', 'todo')
            ->forSite($this->id)
            ->orderByRaw('follow_up_at IS NULL, follow_up_at ASC')
            ->orderBy('sequence')
            ->with(['job'])
            ->get();
    }
}
