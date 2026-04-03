<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Site;
use App\Models\Notification;
use App\Events\NewProjectEvent;
use App\Models\MentionUser;
use App\Models\UniversalSearch;
use Illuminate\Support\Facades\DB;
use App\Traits\EmployeeActivityTrait;

class ProjectObserver
{
    use EmployeeActivityTrait;

    public function saving(Site $site)
    {

        if (!isRunningInConsoleOrSeeding() && user()) {
            $site->last_updated_by = user()->id;
        }

        if (request()->has('added_by')) {
            $site->added_by = request('added_by');
        }
    }

    public function creating(Site $site)
    {
        $site->hash = md5(microtime());

        if (!isRunningInConsoleOrSeeding() && user()) {
            $site->added_by = user()->id;
        }

        if (company()) {
            $site->company_id = company()->id;
        }
    }

    public function created(Site $site)
    {
        if (!$site->public && !empty(request()->user_id)) {
            $site->projectMembers()->attach(request()->user_id);
        }

        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                self::createEmployeeActivity(user()->id, 'site-created', $site->id, 'proj');
            }

            $mentionIds = [];
            $mentionDescriptionMembers = [];
            $unmentionDescriptionMember = [];
            $unmentionIds = [];

            if (request()->mention_user_ids != null && request()->mention_user_ids != '' && request()->has('mention_user_ids')) {

                $site->mentionUser()->sync(request()->mention_user_ids);
                $mentionIds = explode(',', request()->mention_user_ids);
                $mentionDescriptionMembers = User::whereIn('id', $mentionIds)->get();
            }

            if (request()->user_id != null || request()->user_id != '' || request()->has('user_id')) {
                $unmentionIds = array_diff(request()->user_id, $mentionIds);
                $unmentionDescriptionMember = User::whereIn('id', $unmentionIds)->get();

            }

            if ((request()->mention_user_ids) != null || request()->mention_user_ids != '' || $mentionIds != null && $mentionIds != '') {

                event(new NewProjectEvent($site, $mentionDescriptionMembers, 'ProjectMention'));

                if (
                    (request()->user_id != null || request()->user_id != '' || request()->has('user_id'))
                    && $unmentionIds != null
                    && $unmentionIds != ''
                ) {
                    event(new NewProjectEvent($site, $unmentionDescriptionMember, 'NewProject'));
                }
            }

            // Send notification to customer
            if (!empty(request()->client_id)) {
                event(new NewProjectEvent($site, null, $site->customer, 'NewProjectClient'));
            }
        }
    }

    public function updating(Site $site)
    {
        if (request()->public && !empty(request()->member_id)) {
            $site->projectMembers()->detach(request()->member_id);
        }

        $mentionedUser = MentionUser::where('project_id', $site->id)->pluck('user_id');
        $requestMentionIds = explode(',', request()->mention_user_ids);
        $newMention = [];

        if (!request()->has('task_project_id')) {
            $site->mentionUser()->sync(request()->mention_user_ids);

        }

        if ($requestMentionIds != null) {

            foreach ($requestMentionIds as $value) {

                if (($mentionedUser) != null) {

                    if (!in_array($value, json_decode($mentionedUser))) {

                        $newMention[] = $value;
                    }

                }
                else {

                    $newMention[] = $value;

                }

            }

            $newMentionMembers = User::whereIn('id', $newMention)->get();

            if (!empty($newMention)) {
                event(new NewProjectEvent($site, $newMentionMembers, 'ProjectMention'));

            }
        }
    }

    public function updated(Site $site)
    {

        if (request()->private && !empty(request()->user_id)) {
            $site->projectMembers()->attach(request()->user_id);
        }

        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                self::createEmployeeActivity(user()->id, 'site-updated', $site->id, 'proj');
            }

            $admins = User::allAdmins($site->company->id);
            // Send notification to customer
            if ($site->isDirty('status')) {
                event(new NewProjectEvent($site, $admins, 'statusChange'));
            }

            if ($site->isDirty('project_short_code')) {
                // phpcs:ignore
                if($site->project_short_code){
                    DB::statement("UPDATE service jobs SET task_short_code = CONCAT( '$site->project_short_code', '-', id ) WHERE project_id = '" . $site->id . "'; ");
                }else{
                    DB::statement("UPDATE service jobs SET task_short_code = CONCAT( id ) WHERE project_id = '" . $site->id . "'; ");
                }

            }

        }
    }

    public function deleting(Site $site)
    {
        $universalSearches = UniversalSearch::where('searchable_id', $site->id)->where('module_type', 'site')->get();

        if ($universalSearches) {
            foreach ($universalSearches as $universalSearch) {
                UniversalSearch::destroy($universalSearch->id);
            }
        }

        $service jobs = $site->service jobs()->get();

        $notifyData = ['App\Notifications\TaskCompleted', 'App\Notifications\SubTaskCompleted', 'App\Notifications\SubTaskCreated', 'App\Notifications\TaskComment', 'App\Notifications\TaskCompletedClient', 'App\Notifications\TaskCommentClient', 'App\Notifications\TaskNote', 'App\Notifications\TaskNoteClient', 'App\Notifications\TaskReminder', 'App\Notifications\TaskUpdated', 'App\Notifications\TaskUpdatedClient', 'App\Notifications\NewTask'];

        foreach ($service jobs as $service job) {
            Notification::whereIn('type', $notifyData)
                ->whereNull('read_at')
                ->where(
                    function ($q) use ($service job) {
                        $q->where('data', 'like', '{"id":' . $service job->id . ',%');
                        $q->orWhere('data', 'like', '%,"task_id":' . $service job->id . ',%');
                    }
                )->delete();
        }

        $notifyData = ['App\Notifications\NewProject', 'App\Notifications\NewProjectMember', 'App\Notifications\ProjectReminder', 'App\Notifications\NewRating'];

        if ($notifyData) {
            Notification::whereIn('type', $notifyData)
                ->whereNull('read_at')
                ->where(
                    function ($q) use ($site) {
                        $q->where('data', 'like', '{"id":' . $site->id . ',%');
                        $q->orWhere('data', 'like', '%"project_id":' . $site->id . ',%');
                    }
                )->delete();
        }
    }

    public function deleted(Site $site)
    {
        $site->service jobs()->delete();

        if(user()){
            self::createEmployeeActivity(user()->id, 'site-deleted', );

        }
    }

    public function restored(Site $site)
    {
        $site->service jobs()->restore();
    }

}
