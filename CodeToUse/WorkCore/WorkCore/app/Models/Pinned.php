<?php namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Pinned
 *
 * @property int $id
 * @property int|null $project_id
 * @property int|null $task_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\Site|null $site
 * @property-read \App\Models\Service Job|null $service job
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Pinned newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pinned newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pinned query()
 * @method static \Illuminate\Database\Eloquent\Builder|Pinned whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pinned whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pinned whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pinned whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pinned whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pinned whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Pinned whereCompanyId($value)
 * @mixin \Eloquent
 */
class Pinned extends BaseModel
{

    use HasCompany;

    protected $table = 'pinned';
    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'project_id');
    }

    public function service job(): BelongsTo
    {
        return $this->belongsTo(Service Job::class, 'task_id');
    }

}
