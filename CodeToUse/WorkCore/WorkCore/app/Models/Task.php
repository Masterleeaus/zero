<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use App\Helper\UserService;

/**
 * App\Models\Service Job
 *
 * @property int $id
 * @property string $heading
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property int|null $project_id
 * @property int|null $task_category_id
 * @property string $priority
 * @property string $status
 * @property int|null $board_column_id
 * @property int $column_priority
 * @property \Illuminate\Support\Carbon|null $completed_on
 * @property int|null $created_by
 * @property int|null $recurring_task_id
 * @property-read \Illuminate\Database\Eloquent\Collection|Service Job[] $recurrings
 * @property-read int|null $recurrings_count
 * @property int|null $dependent_task_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $milestone_id
 * @property int $is_private
 * @property int $billable
 * @property int $estimate_hours
 * @property int $estimate_minutes
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectTimeLog[] $activeTimerAll
 * @property-read int|null $active_timer_all_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectTimeLog[] $approvedTimeLogs
 * @property-read int|null $approved_time_logs_count
 * @property-read \App\Models\TaskboardColumn|null $boardColumn
 * @property-read \App\Models\TaskCategory|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TaskComment[] $comments
 * @property-read int|null $comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SubTask[] $completedSubtasks
 * @property-read int|null $completed_subtasks_count
 * @property-read \App\Models\User|null $createBy
 * @property-read \App\Models\User|null $addedByUser
 * @property-read \App\Models\ProjectMilestone|null $milestone
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TaskFile[] $files
 * @property-read int|null $files_count
 * @property-read mixed $create_on
 * @property-read string $due_on
 * @property-read mixed $extras
 * @property-read mixed $icon
 * @property-read mixed $is_task_user
 * @property-read mixed $total_estimated_minutes
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TaskHistory[] $history
 * @property-read int|null $history_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SubTask[] $incompleteSubtasks
 * @property-read int|null $incomplete_subtasks_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TaskLabel[] $label
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TaskUser[] $taskUsers
 * @property-read int|null $label_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TaskLabelList[] $labels
 * @property-read int|null $labels_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TaskNote[] $notes
 * @property-read int|null $notes_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Site|null $site
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SubTask[] $checklists
 * @property-read int|null $subtasks_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectTimeLog[] $timeLogged
 * @property-read int|null $time_logged_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job pending()
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job query()
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereBillable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereBoardColumnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereColumnPriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereCompletedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereDependentTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereEstimateHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereEstimateMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereHeading($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereIsPrivate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereMilestoneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereRecurringTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereTaskCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereUpdatedAt($value)
 * @property string|null $hash
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereHash($value)
 * @property int $repeat
 * @property int $repeat_complete
 * @property int|null $repeat_count
 * @property string $repeat_type
 * @property int|null $repeat_cycles
 * @property-read \App\Models\ProjectTimeLog|null $activeTimer
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $activeUsers
 * @property-read int|null $active_users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereRepeat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereRepeatComplete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereRepeatCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereRepeatCycles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereRepeatType($value)
 * @property string|null $event_id
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereEventId($value)
 * @property int|null $company_id
 * @property string|null $task_short_code
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Site|null $activeProject
 * @property-read \App\Models\Company|null $company
 * @property-read int|null $task_users_count
 * @method static \Illuminate\Database\Query\Builder|Service Job onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Job whereTaskShortCode($value)
 * @method static \Illuminate\Database\Query\Builder|Service Job withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Service Job withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionTask
 * @property-read int|null $mention_task_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read int|null $mention_user_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionTask
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @mixin \Eloquent
 */
class Service Job extends BaseModel
{

    use Notifiable, SoftDeletes;
    use CustomFieldsTrait;
    use HasCompany;

    protected $casts = [
        'due_date' => 'datetime',
        'completed_on' => 'datetime',
        'start_date' => 'datetime',
    ];
    protected $appends = ['due_on', 'create_on'];
    protected $guarded = ['id'];
    protected $with = ['company:id,date_format', 'site:id,project_name,need_approval_by_admin,project_short_code', 'users:id,name,image'];

    const CUSTOM_FIELD_MODEL = 'App\Models\Service Job';

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'project_id')->withTrashed();
    }

    public function recurringTask(): BelongsTo
    {
        return $this->belongsTo(Service Job::class, 'recurring_task_id');
    }

    public function activeProject(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'project_id');
    }

    public function label(): HasMany
    {
        return $this->hasMany(TaskLabel::class, 'task_id');
    }

    public function boardColumn(): BelongsTo
    {
        return $this->belongsTo(TaskboardColumn::class, 'board_column_id');
    }

    public function dependentTask()
    {
        return $this->belongsTo(Service Job::class, 'dependent_task_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_users')->withoutGlobalScope(ActiveScope::class)->using(TaskUser::class);
    }

    public function taskUsers(): HasMany
    {
        return $this->hasMany(TaskUser::class, 'task_id');
    }

    public function activeUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_users')->using(TaskUser::class);
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(TaskLabelList::class, 'task_labels', 'task_id', 'label_id');
    }

    public function createBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withoutGlobalScope(ActiveScope::class);
    }

    public function addedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by')->withoutGlobalScope(ActiveScope::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TaskCategory::class, 'task_category_id');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(SubTask::class, 'task_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(TaskHistory::class, 'task_id')->orderByDesc('id');
    }

    public function completedSubtasks(): HasMany
    {
        return $this->hasMany(SubTask::class, 'task_id')->where('sub_tasks.status', 'complete');
    }

    public function incompleteSubtasks(): HasMany
    {
        return $this->hasMany(SubTask::class, 'task_id')->where('sub_tasks.status', 'incomplete');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class, 'task_id')->orderByDesc('id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(TaskNote::class, 'task_id')->orderByDesc('id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(TaskFile::class, 'task_id')->orderByDesc('id');
    }

    public function activeTimer(): HasOne
    {
        return $this->hasOne(ProjectTimeLog::class, 'task_id')
            ->whereNull('project_time_logs.end_time');
    }

    public function userActiveTimer(): HasOne
    {
        return $this->hasOne(ProjectTimeLog::class, 'task_id')
            ->whereNull('project_time_logs.end_time')
            ->where('project_time_logs.user_id', user()->id);
    }

    public function activeTimerAll(): HasMany
    {
        return $this->hasMany(ProjectTimeLog::class, 'task_id')
            ->whereNull('project_time_logs.end_time');
    }

    public function timeLogged(): HasMany
    {
        return $this->hasMany(ProjectTimeLog::class, 'task_id');
    }

    public function approvedTimeLogs(): HasMany
    {
        return $this->hasMany(ProjectTimeLog::class, 'task_id')->where('project_time_logs.approved', 1)->orderBy('project_time_logs.start_time', 'desc');
    }

    public function recurrings(): HasMany
    {
        return $this->hasMany(Service Job::class, 'recurring_task_id');
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ProjectMilestone::class, 'milestone_id');
    }

    public function scopePending($query)
    {
        $taskBoardColumn = TaskboardColumn::completeColumn();

        return $query->where('service jobs.board_column_id', '<>', $taskBoardColumn->id);
    }

    /**
     * @return string
     */
    public function getDueOnAttribute()
    {
        if (is_null($this->due_date)) {
            return '';
        }

        // company relation is null
        if (is_null($this->company_id)) {
            return $this->due_date->format('Y-m-d');
        }

        return $this->due_date->format($this->company->date_format);

    }

    public function getCreateOnAttribute()
    {
        if (is_null($this->start_date)) {
            return '';
        }

        return $this->start_date->format($this->company->date_format);
    }

    public function getIsTaskUserAttribute()
    {
        if (user()) {
            return $this->taskUsers->where('user_id', user()->id)->first();
        }
    }

    public function getTotalEstimatedMinutesAttribute()
    {
        $hours = $this->estimate_hours;
        $minutes = $this->estimate_minutes;

        return ($hours * 60) + $minutes;
    }

    /**
     * @param int $projectId
     * @param null $userID
     * @return \Illuminate\Support\Collection
     */
    public static function projectOpenTasks($projectId, $userID = null)
    {
        $taskBoardColumn = TaskboardColumn::completeColumn();
        $projectTask = Service Job::join('task_users', 'task_users.task_id', '=', 'service jobs.id')
            ->where('service jobs.board_column_id', '<>', $taskBoardColumn->id)
            ->select('service jobs.*');

        if ($userID) {
            $projectIssue = $projectTask->where('task_users.user_id', '=', $userID);
        }

        $projectIssue = $projectTask->where('project_id', $projectId)
            ->get();

        return $projectIssue;
    }

    public static function projectCompletedTasks($projectId)
    {
        $taskBoardColumn = TaskboardColumn::completeColumn();

        return Service Job::where('service jobs.board_column_id', $taskBoardColumn->id)
            ->where('project_id', $projectId)
            ->get();
    }

    public static function projectTasks($projectId, $userID = null, $onlyPublic = null, $withoutDueDate = null)
    {

        $projectTask = Service Job::with('boardColumn')
            ->leftJoin('task_users', 'task_users.task_id', '=', 'service jobs.id')
            ->where('project_id', $projectId)
            ->select('service jobs.*');

        if ($userID) {
            $projectIssue = $projectTask->where('task_users.user_id', '=', $userID);
        }

        if ($withoutDueDate) {
            $projectIssue = $projectTask->whereNotNull('service jobs.due_date');
        }

        if ($onlyPublic != null) {
            $projectIssue = $projectTask->where(
                function ($q) {
                    $q->where('is_private', 0);

                    if (auth()->user()) {
                        $q->orWhere('created_by', user()->id);
                    }
                }
            );
        }

        $projectIssue = $projectTask->select('service jobs.*');
        $projectIssue = $projectTask->orderBy('start_date', 'asc');
        $projectIssue = $projectTask->groupBy('service jobs.id');
        $projectIssue = $projectTask->get();

        return $projectIssue;
    }

    public static function projectLogTimeTasks($projectId, $userID = null, $onlyPublic = null, $withoutDueDate = null)
    {

        $projectTask = Service Job::with('boardColumn')
            ->leftJoin('task_users', 'task_users.task_id', '=', 'service jobs.id')
            ->where('project_id', $projectId)
            ->select('service jobs.*');

        if ($userID) {
            $projectIssue = $projectTask->where('task_users.user_id', '=', $userID);
        }

        if ($withoutDueDate) {
            $projectIssue = $projectTask->whereNotNull('service jobs.due_date');
        }

        if ($onlyPublic != null) {
            $projectIssue = $projectTask->where(
                function ($q) {
                    $q->where('is_private', 0);

                    if (auth()->user()) {
                        $q->orWhere('created_by', user()->id);
                    }
                }
            );
        }

        // Get service jobs related to selected site and selected site manager
        $viewTaskPermission = user()->permission('view_tasks');
        $addTimelogPermission = user()->permission('add_timelogs');

        if ($viewTaskPermission == 'both') {
            $projectTask->where(function ($query) use ($addTimelogPermission) {
                if ($addTimelogPermission == 'all') {
                    $query->where('service jobs.added_by', user()->id);
                }

                $query->orWhere('task_users.user_id', user()->id);
            });
        }

        $projectIssue = $projectTask->select('service jobs.*');
        $projectIssue = $projectTask->orderBy('start_date');
        $projectIssue = $projectTask->groupBy('service jobs.id');
        $projectIssue = $projectTask->get();

        return $projectIssue;
    }

    /**
     * @return bool
     */
    public function pinned()
    {
        $userId = UserService::getUserId();
        $pin = Pinned::where('user_id', $userId)->where('task_id', $this->id)->first();

        if (!is_null($pin)) {
            return true;
        }

        return false;
    }

    public static function timelogTasks($projectId = null)
    {
        $viewTaskPermission = user()->permission('view_tasks');
        $addTimelogPermission = user()->permission('add_timelogs');

        if ($viewTaskPermission != 'none' && $addTimelogPermission != 'none') {
            $service jobs = Service Job::select('service jobs.id', 'service jobs.heading')
                ->join('task_users', 'task_users.task_id', '=', 'service jobs.id');

            if (!is_null($projectId)) {
                $service jobs->where('service jobs.project_id', '=', $projectId);
            }

            if ($viewTaskPermission == 'both') {
                $service jobs->where(function ($query) use ($addTimelogPermission) {
                    if ($addTimelogPermission == 'all') {
                        $query->where('service jobs.added_by', user()->id);
                    }

                    $query->orWhere('task_users.user_id', user()->id);
                });
            }

            if ($viewTaskPermission == 'added' && $addTimelogPermission == 'all') {
                $service jobs->where('service jobs.added_by', user()->id);
            }

            if ($viewTaskPermission == 'owned') {
                $service jobs->where('task_users.user_id', user()->id);
            }

            return $service jobs->groupBy('service jobs.id')->get();
        }
    }

    public function breakMinutes()
    {
        return ProjectTimeLogBreak::taskBreakMinutes($this->id);
    }

    public function mentionUser(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mention_users')->withoutGlobalScope(ActiveScope::class)->using(MentionUser::class);
    }

    public function mentionTask(): HasMany
    {
        return $this->hasMany(MentionUser::class, 'task_id');
    }

    public static function projectTaskCount($projectID)
    {
        $service job = Service Job::where('project_id', $projectID)->orderByDesc('id')->first();

        if ($service job) {
            $taskID = explode('-', $service job->task_short_code);
            $taskCode = array_pop($taskID);

            return (int)$taskCode;
        }

        return 0;

    }

    /*
     * Permissions
     */
    public function hasAllPermission($permission): bool
    {
        return $permission == 'all';
    }

    public function hasAddedPermission($permission): bool
    {
        $userId = UserService::getUserId();
        $id = user()->id;

        if (in_array('customer', user_roles())) {
            $clientContact = ClientContact::where('client_id', user()->id)->first();
            if ($clientContact) {
                $id = $clientContact->user_id;
            }
        }
        return $permission == 'added' && ($id == $this->added_by || $userId == $this->added_by);
    }

    public function hasOwnedPermission($permission): bool
    {
        $taskUsers = $this->users->pluck('id')->toArray();
        $userId = UserService::getUserId();
        return $permission == 'owned' && (in_array(user()->id, $taskUsers) || in_array($userId, $taskUsers) || in_array('customer', user_roles()));
    }

    public function hasBothPermission($permission): bool
    {
        $taskUsers = $this->users->pluck('id')->toArray();
        $userId = UserService::getUserId();
        $id = user()->id;

        if (in_array('customer', user_roles())) {
            $clientContact = ClientContact::where('client_id', user()->id)->first();
            if ($clientContact) {
                $id = $clientContact->user_id;
            }
        }

        return $permission == 'both' && (in_array(user()->id, $taskUsers) || ($this->added_by == $id || $this->added_by == $userId) || in_array('customer', user_roles()));
    }

    public function projectAdmin(): bool
    {
        return $this->project_admin === user()->id;
    }

    public function canViewTicket(): bool
    {
        return $this->hasPermission(user()->permission('view_tasks'));
    }

    public function canDeleteTicket(): bool
    {
        return $this->hasPermission(user()->permission('delete_tasks'));
    }

    public function canEditTicket(): bool
    {
        return $this->hasPermission(user()->permission('edit_tasks'));
    }

    public function hasPermission($permission): bool
    {
        return $this->hasAllPermission($permission) ||
            $this->hasAddedPermission($permission) ||
            $this->hasOwnedPermission($permission) ||
            $this->hasBothPermission($permission) ||
            $this->projectAdmin();
    }

}
