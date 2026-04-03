<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helper\UserService;

/**
 * App\Models\Site
 *
 * @property int $id
 * @property string $project_name
 * @property string|null $project_summary
 * @property int|null $project_admin
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon|null $deadline
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectNote[] $notes
 * @property int|null $category_id
 * @property int|null $client_id
 * @property int|null $team_id
 * @property string|null $feedback
 * @property string $manual_timelog
 * @property string $client_view_task
 * @property string $allow_client_notification
 * @property int $completion_percent
 * @property string $calculate_task_progress
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property float|null $project_budget
 * @property int|null $currency_id
 * @property float|null $hours_allocated
 * @property string $status
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\ProjectCategory|null $category
 * @property-read \App\Models\User|null $customer
 * @property-read \App\Models\ClientDetails|null $clientdetails
 * @property-read \App\Models\Currency|null $currency
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Discussion[] $discussions
 * @property-read int|null $discussions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Expense[] $expenses
 * @property-read int|null $expenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectFile[] $files
 * @property-read int|null $files_count
 * @property-read mixed $extras
 * @property-read mixed $icon
 * @property-read mixed $is_project_admin
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Issue[] $issues
 * @property-read int|null $issues_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectMember[] $members
 * @property-read int|null $members_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $projectMembers
 * @property-read int|null $members_many_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectMilestone[] $milestones
 * @property-read int|null $milestones_count
 * @property-read int|null $notes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Payment[] $payments
 * @property-read int|null $payments_count
 * @property-read \App\Models\ProjectRating|null $rating
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Service Job[] $service jobs
 * @property-read int|null $tasks_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectTimeLog[] $times
 * @property-read int|null $times_count
 * @method static \Illuminate\Database\Eloquent\Builder|Site canceled()
 * @method static \Illuminate\Database\Eloquent\Builder|Site completed()
 * @method static \Database\Factories\ProjectFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Site finished()
 * @method static \Illuminate\Database\Eloquent\Builder|Site inProcess()
 * @method static \Illuminate\Database\Eloquent\Builder|Site newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Site newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Site notStarted()
 * @method static \Illuminate\Database\Eloquent\Builder|Site onHold()
 * @method static \Illuminate\Database\Query\Builder|Site onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Site overdue()
 * @method static \Illuminate\Database\Eloquent\Builder|Site query()
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereAllowClientNotification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereCalculateTaskProgress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereClientViewTask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereCompletionPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereFeedback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereHoursAllocated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereManualTimelog($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereProjectAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereProjectBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereProjectName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereProjectSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Site withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Site withoutTrashed()
 * @property string|null $hash
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereHash($value)
 * @property int $public
 * @method static \Illuminate\Database\Eloquent\Builder|Site wherePublic($value)
 * @property int|null $company_id
 * @property string|null $project_short_code
 * @property int $enable_miroboard
 * @property string|null $miro_board_id
 * @property int $client_access
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereClientAccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereEnableMiroboard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereMiroBoardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereProjectShortCode($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Service Agreement> $service agreements
 * @property-read int|null $contracts_count
 * @property-read int|null $project_members_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionNote
 * @property-read int|null $mention_note_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read int|null $mention_user_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProjectMilestone> $incompleteMilestones
 * @property-read int|null $incomplete_milestones_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionProject
 * @property-read int|null $mention_project_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read \App\Models\Site|null $due_date
 * @mixin \Eloquent
 */
class Site extends BaseModel
{

    use CustomFieldsTrait, HasFactory;
    use SoftDeletes;
    use HasCompany;

    protected $casts = [
        'start_date' => 'datetime',
        'deadline' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected $guarded = ['id'];
    protected $with = [];
    protected $appends = ['isProjectAdmin'];

    const CUSTOM_FIELD_MODEL = 'App\Models\Site';

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'category_id');
    }

    public function projectAdmin()
    {
        return $this->belongsTo(User::class, 'project_admin');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function clientdetails(): BelongsTo
    {
        return $this->belongsTo(ClientDetails::class, 'client_id', 'user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class, 'project_id');
    }

    public function projectMembersWithoutScope(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')->using(ProjectMember::class)->withoutGlobalScope(ActiveScope::class);
    }

    public function projectMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')->using(ProjectMember::class);
    }

    public function service jobs(): HasMany
    {
        return $this->hasMany(Service Job::class, 'project_id')->orderByDesc('id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class, 'project_id')->orderByDesc('id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'project_id')->orderByDesc('id');
    }

    public function label(): HasMany
    {
        return $this->hasMany(ProjectLabel::class, 'project_id');
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(ProjectLabelList::class, 'project_labels', 'project_id', 'label_id');
    }

    public function service agreements(): HasMany
    {
        return $this->hasMany(Service Agreement::class, 'project_id')->orderByDesc('id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class, 'project_id')->orderByDesc('id');
    }

    public function times(): HasMany
    {
        return $this->hasMany(ProjectTimeLog::class, 'project_id')->with('breaks')->orderByDesc('id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class, 'project_id')->orderByDesc('id');
    }

    public function incompleteMilestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class, 'project_id')->whereNot('status', 'complete')->orderByDesc('id');
    }

    public function completedMilestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class, 'project_id')->where('status', 'complete')->orderByDesc('id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'project_id')->orderByDesc('id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ProjectNote::class, 'project_id')->orderByDesc('id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'project_id')->orderByDesc('id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(Discussion::class, 'project_id')->orderByDesc('id');
    }

    public function rating(): HasOne
    {
        return $this->hasOne(ProjectRating::class);
    }

    /**
     * @return bool
     */
    public function checkProjectUser()
    {
        return ProjectMember::where('project_id', $this->id)
            ->where('user_id', user()->id)
            ->exists();
    }

    /**
     * @return bool
     */
    public function checkProjectClient()
    {
        return Site::where('id', $this->id)
            ->where('client_id', user()->id)
            ->exists();

    }

    public static function clientProjects($clientId)
    {
        return Site::where('client_id', $clientId)->get();
    }

    /**
     * @param boolean $notFinished
     * Search Parameter is passed the get only search results and 20
     * @return \Illuminate\Support\Collection
     */
    public static function allProjects($notFinished = false)
    {
        $sites = Site::query();
        $userId = UserService::getUserId();

        if ($notFinished) {
            $sites->notFinished();
        }

        $sites = $sites->leftJoin('project_members', 'project_members.project_id', 'sites.id')
            ->select('sites.*')
            ->orderBy('project_name');


        if (!isRunningInConsoleOrSeeding()) {

            if (user()->permission('view_projects') == 'added') {
                $sites->where('sites.added_by', $userId)->orWhere('sites.public', 1);
            }

            if (user()->permission('view_projects') == 'added' && in_array('customer', user_roles())) {
                $sites->where('sites.added_by', $userId);
            }

            if (user()->permission('view_projects') == 'both') {
                $sites->where('sites.added_by', $userId)->orWhere('project_members.user_id', $userId)->orWhere('sites.public', 1);
            }

            if (user()->permission('view_projects') == 'both' && in_array('customer', user_roles())) {
                $sites->where('sites.added_by', $userId)->orWhere('sites.client_id', $userId);
            }

            if (user()->permission('view_projects') == 'owned' && in_array('cleaner', user_roles())) {
                $sites->where('project_members.user_id', $userId)->orWhere('sites.public', 1);
            }

            if (user()->permission('view_projects') == 'owned' && in_array('customer', user_roles())) {
                $sites->where('sites.client_id', $userId);
            }

            if (user()->permission('view_projects') == 'all' && in_array('customer', user_roles())) {
                $sites->where('sites.client_id', $userId);
            }
        }

        $sites = $sites->groupBy('sites.id');

        // @codingStandardsIgnoreStart
        //        if ($search !== '') {
        //            return $sites->where('project_name', 'like', '%' . $search . '%')
        //                ->take(GlobalSetting::SELECT2_SHOW_COUNT)
        //                ->get();
        //        }
        // @codingStandardsIgnoreEnd

        return $sites->get();
    }

    public static function allProjectsHavingClient()
    {
        $sites = Site::with('currency')->leftJoin('project_members', 'project_members.project_id', 'sites.id')
            ->whereNotNull('client_id')
            ->select('sites.*')
            ->orderBy('project_name');

        if (!isRunningInConsoleOrSeeding()) {

            if (user()->permission('view_projects') == 'added') {
                $sites->where('sites.added_by', user()->id);
            }

            if (user()->permission('view_projects') == 'owned' && in_array('cleaner', user_roles())) {
                $sites->where('project_members.user_id', user()->id);
            }

            if (user()->permission('view_projects') == 'owned' && in_array('customer', user_roles())) {
                $sites->where('sites.client_id', user()->id);
            }
        }

        return $sites->groupBy('sites.id')->get();
    }

    public static function byEmployee($employeeId)
    {
        return Site::join('project_members', 'project_members.project_id', '=', 'sites.id')
            ->where('project_members.user_id', $employeeId)
            ->select('sites.*')
            ->get();
    }

    public function scopeCompleted($query)
    {
        return $query->where('completion_percent', '100');
    }

    public function scopeInProcess($query)
    {
        return $query->where('status', 'in progress');
    }

    public function scopeOnHold($query)
    {
        return $query->where('status', 'on hold');
    }

    public function scopeFinished($query)
    {
        return $query->where('status', 'finished');
    }

    public function scopeNotFinished($query)
    {
        return $query->where('status', '<>', 'finished');
    }

    public function scopeNotStarted($query)
    {
        return $query->where('status', 'not started');
    }

    public function scopeCanceled($query)
    {
        return $query->where('status', 'canceled');
    }

    public function scopeOverdue($query)
    {
        $setting = company();

        return $query->where('completion_percent', '<>', '100')
            ->where('deadline', '<', Carbon::today()->timezone($setting->timezone));
    }

    public function getIsProjectAdminAttribute()
    {
        if (auth()->user() && $this->project_admin == user()->id) {
            return true;
        }

        return false;
    }

    public function pinned()
    {
        $userId = UserService::getUserId();
        $pin = Pinned::where('user_id', $userId)->where('project_id', $this->id)->first();

        if (!is_null($pin)) {
            return true;
        }

        return false;
    }

    public function mentionUser(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mention_users')->withoutGlobalScope(ActiveScope::class)->using(MentionUser::class);
    }

    public function mentionProject(): HasMany
    {
        return $this->hasMany(MentionUser::class, 'project_id');
    }

    public function zones(): HasMany
    {
        return $this->hasMany(ProjectDepartment::class, 'project_id');
    }

    public function projectDepartments(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'project_departments')->using(ProjectDepartment::class);
    }

}
