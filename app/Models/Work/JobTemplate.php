<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Team\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobTemplate extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'job_type_id',
        'team_id',
        'name',
        'instructions',
        'duration',
    ];

    protected $casts = [
        'duration' => 'float',
    ];

    public function jobType(): BelongsTo
    {
        return $this->belongsTo(JobType::class, 'job_type_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Activity definitions attached to this template.
     *
     * When a job is created from this template, these activities are
     * copied as live JobActivity rows on the resulting ServiceJob.
     */
    public function templateActivities(): HasMany
    {
        return $this->hasMany(JobActivity::class, 'template_id')->orderBy('sequence');
    }

    public function scopeForSelect(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    /**
     * Instantiate a new ServiceJob from this template.
     *
     * Applies template defaults (duration, job type, team, notes) to
     * the given attribute overrides and returns an unsaved ServiceJob
     * instance. The caller must persist it.
     *
     * @param  array<string, mixed>  $overrides  Extra / override attributes
     */
    public function instantiateJob(array $overrides = []): ServiceJob
    {
        return new ServiceJob(array_merge([
            'company_id'         => $this->company_id,
            'template_id'        => $this->id,
            'job_type_id'        => $this->job_type_id,
            'team_id'            => $this->team_id,
            'title'              => $this->name,
            'notes'              => $this->instructions,
            'scheduled_duration' => $this->duration,
        ], $overrides));
    }

    /**
     * Copy this template's activity definitions onto a saved ServiceJob.
     *
     * Should be called after the ServiceJob is persisted so that the
     * FK (service_job_id) can be set correctly.
     *
     * @param  ServiceJob  $job  The job that was just created from this template
     */
    public function copyActivitiesTo(ServiceJob $job): void
    {
        foreach ($this->templateActivities as $activity) {
            JobActivity::create([
                'company_id'     => $job->company_id,
                'service_job_id' => $job->id,
                'name'           => $activity->name,
                'ref'            => $activity->ref,
                'sequence'       => $activity->sequence,
                'required'       => $activity->required,
                'state'          => 'todo',
                'completed'      => false,
            ]);
        }
    }
}
