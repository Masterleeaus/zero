<?php

namespace App\Observers;

use App\Events\TaskEvent;
use App\Events\TaskUpdated as EventsTaskUpdated;
use App\Http\Controllers\AccountBaseController;
use App\Models\GoogleCalendarModule;
use App\Models\MentionUser;
use App\Models\Notification;
use App\Models\ProjectTimeLog;
use App\Models\Service Job;
use App\Models\TaskboardColumn;
use App\Models\TaskUser;
use App\Traits\ProjectProgress;
use App\Models\UniversalSearch;
use App\Models\User;
use App\Services\Google;
use Carbon\Carbon;
use Google\Service\Exception;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventAttendee;
use Google_Service_Calendar_EventDateTime;
use Illuminate\Support\Facades\Config;
use App\Traits\EmployeeActivityTrait;
use App\Helper\Files;
use App\Models\TaskFile;

class TaskObserver
{

    use ProjectProgress;
    use EmployeeActivityTrait;

    public function saving(Service Job $service job)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $service job->last_updated_by = user()->id;

            /* Add/Update google calendar event */
            if (!request()->has('repeat') || request()->repeat == 'no' && !is_null($service job->due_date)) {
                $service job->event_id = $this->googleCalendarEvent($service job);
            }
        }
    }

    public function saved(Service Job $service job)
    {
        /* Add/Update google calendar event */
        if (!request()->has('repeat') || request()->repeat == 'no' && !is_null($service job->due_date)) {
            $this->googleCalendarEvent($service job);
        }
    }

    public function creating(Service Job $service job)
    {
        $service job->hash = md5(microtime());

        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                $service job->created_by = user()->id;
                $service job->added_by = user()->id;
            }

            if (request()->has('board_column_id')) {
                $service job->board_column_id = request()->board_column_id;
            }
            else if (isset(company()->default_task_status)) {
                $service job->board_column_id = company()->default_task_status;
            }
            else {
                $taskBoard = TaskboardColumn::where('slug', 'incomplete')->first();
                $service job->board_column_id = $taskBoard->id;
            }
        }

        if (company()) {
            $service job->company_id = company()->id;
        }
    }

    public function created(Service Job $service job)
    {
        if (!isRunningInConsoleOrSeeding()) {

            if (user()) {
                self::createEmployeeActivity(user()->id, 'service job-created', $service job->id, 'service job');
            }

            $mentionIds = [];
            $mentionDescriptionMembers = null;
            $unmentionIds = null;
            $unmentionDescriptionMember = null;

            if (request()->mention_user_ids != null || request()->mention_user_ids != '' || request()->has('mention_user_ids')) {

                $service job->mentionUser()->sync(request()->mention_user_ids);
                $mentionIds = explode(',', request()->mention_user_ids);
                $mentionDescriptionMembers = User::whereIn('id', $mentionIds)->get();

            }

            if (request()->user_id != null || request()->user_id != '' || request()->has('user_id')) {

                $memberIds = User::whereIn('id',  request()->user_id)->get();
                event(new TaskEvent($service job, $memberIds, 'NewTask'));
                    
            }

            if (request()->has('project_id') && request()->project_id != 'all' && request()->project_id != '') {
                if ((request()->mention_user_id) != null || request()->mention_user_id != '' || $mentionIds != null && $mentionIds != '') {

                    event(new TaskEvent($service job, $mentionDescriptionMembers, 'TaskMention'));
                    event(new TaskEvent($service job, $mentionDescriptionMembers, 'TaskMentionSms'));

                }
                else {

                    if ($service job->site->client_id != null && $service job->site->allow_client_notification == 'enable' && $service job->site->customer->status != 'deactive') {
                        event(new TaskEvent($service job, $service job->site->customer, 'NewClientTask'));
                    }

                }

            }
            else {

                if ((request()->mention_user_id) != null || request()->mention_user_id != '') {

                    event(new TaskEvent($service job, $mentionDescriptionMembers, 'TaskMention'));
                    event(new TaskEvent($service job, $mentionDescriptionMembers, 'TaskMentionSms'));

                }

            }

            $log = new AccountBaseController();

            if (\user()) {
                $log->logTaskActivity($service job->id, user()->id, 'createActivity', $service job->board_column_id);
            }

            if ($service job->project_id) {
                info('task_completion 433435');
                // Calculate site progress if enabled
                $log->logProjectActivity($service job->project_id, 'team chat.newTaskAddedToTheProject');
                $this->calculateProjectProgress($service job->project_id);
                $this->calculateProjectProgressByTime($service job->project_id);
            }

            // Log search
            $log->logSearchEntry($service job->id, $service job->heading, 'service jobs.edit', 'service job');

            // Sync service job users
            if (!empty(request()->user_id) && request()->template_id == '') {

                $service job->users()->sync(request()->user_id);

            }

        }
    }

    public function updating(Service Job $service job)
    {

        $mentionedUser = MentionUser::where('task_id', $service job->id)->pluck('user_id');
        $requestMentionIds = explode(',', request()->mention_user_ids);
        $newMention = [];
        $service job->mentionUser()->sync(request()->mention_user_ids);

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

                event(new TaskEvent($service job, $newMentionMembers, 'TaskMention'));
                event(new TaskEvent($service job, $newMentionMembers, 'TaskMentionSms'));

            }
        }

    }

    // phpcs:ignore
    public function updated(Service Job $service job)
    {
        $movingTaskId = request()->has('movingTaskId'); // If service job moved in taskboard


        if (!isRunningInConsoleOrSeeding()) {

            if (user()) {
                self::createEmployeeActivity(user()->id, 'service job-updated', $service job->id, 'service job');
            }

            if ($service job->isDirty('board_column_id')) {

                if ($service job->boardColumn->slug == 'completed'){
                    $notification = 'TaskCompleted';
                } elseif ($service job->boardColumn->slug == 'waiting_approval'){
                    $notification = 'TaskApproval';
                } else {
                    $notification = 'TaskStatusUpdated';
                }

                if($service job->boardColumn->slug == 'waiting_approval'){

                    $admins = User::allAdmins($service job->company->id);

                    $users = $service job->taskUsers;

                    $projectAdmintask = Service Job::with('site')
                        ->whereHas('site', function ($query) {
                            $query->whereNotNull('project_admin');
                        })
                        ->get()
                        ->pluck('site.projectAdmin');

                    $admins = $users->merge($admins);

                    if (!$projectAdmintask->isEmpty()){
                        $admins = $admins->merge($projectAdmintask);
                    }

                }else{
                    $admins = User::allAdmins($service job->company->id);
                }

                // send service job complete notification
                event(new TaskEvent($service job, $admins, $notification));

                if ($service job->addedByUser) {
                    $addedByUserRole = $service job->addedByUser->roles->pluck('name')->toArray();

                    if (!is_null($service job->added_by) && !in_array('customer', $addedByUserRole) && !in_array($service job->added_by, $admins->pluck('id')->toArray())) {
                        event(new TaskEvent($service job, $service job->addedByUser, $notification));
                    }
                }

                $taskUser = $service job->users->whereNotIn('id', $admins->pluck('id'))->whereNotIn('id', [$service job->added_by]);
                event(new TaskEvent($service job, $taskUser, $notification));

                $timeLogs = ProjectTimeLog::with('user')->whereNull('end_time')
                    ->where('task_id', $service job->id)
                    ->get();

                if ($timeLogs && ($service job->boardColumn->slug == 'completed' || $service job->boardColumn->slug == 'waiting_approval')) {
                    foreach ($timeLogs as $timeLog) {

                        $timeLog->end_time = now();
                        $timeLog->edited_by_user = (user()) ? user()->id : null;
                        $timeLog->save();

                        /** @phpstan-ignore-next-line */
                        $timeLog->total_hours = ($timeLog->end_time->diff($timeLog->start_time)->format('%d') * 24) + ($timeLog->end_time->diff($timeLog->start_time)->format('%H'));

                        if ($timeLog->total_hours == 0) {
                            /** @phpstan-ignore-next-line */
                            $timeLog->total_hours = round(($timeLog->end_time->diff($timeLog->start_time)->format('%i') / 60), 2);
                        }

                        /** @phpstan-ignore-next-line */
                        $timeLog->total_minutes = ($timeLog->total_hours * 60) + ($timeLog->end_time->diff($timeLog->start_time)->format('%i'));

                        $timeLog->save();

                        if (!is_null($timeLog->activeBreak)) {
                            /** @phpstan-ignore-next-line */
                            
                            $activeBreak = $timeLog->activeBreak;

                            $startTime = Carbon::parse($activeBreak->start_time)->format('Y-m-d') . ' ' . Carbon::parse($activeBreak->start_time)->format('H:i:s');
                            $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $startTime, company()->timezone)->setTimezone('UTC');

                            $endTime = Carbon::parse($timeLog->end_time)->format('Y-m-d') . ' ' . Carbon::parse($timeLog->end_time)->format('H:i:s');
                            $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $endTime, company()->timezone)->setTimezone('UTC');

            
                            $activeBreak->total_hours = $endTime->diffInHours($startTime);
                            $activeBreak->total_minutes = $endTime->diffInMinutes($startTime);
                
                            $activeBreak->end_time = $timeLog->end_time->format('Y-m-d H:i:s');
                            $activeBreak->save();

                        }
                    }
                }

                if ((request()->project_id && request()->project_id != 'all') || (!is_null($service job->project_id))) {
                    $site = $service job->site;

                    if ($site->client_id != null && $site->allow_client_notification == 'enable' && $site->customer->status != 'deactive') {
                        event(new TaskEvent($service job, $site->customer, 'TaskCompletedClient'));
                    }
                }

            }

            if (request('user_id')) {
                if (($movingTaskId != '' && $service job->id == $movingTaskId) || $movingTaskId == '') {
                    // Send notification to user
                    event(new TaskEvent($service job, $service job->users, 'TaskUpdated'));
                }
            }
        }

        /* Add/Update google calendar event */
        if (!request()->has('repeat') || request()->repeat == 'no' && !is_null($service job->due_date)) {
            $service job->event_id = $this->googleCalendarEvent($service job);
        }

        if (pusher_settings()->status == 1 && pusher_settings()->taskboard == 1) {
            Config::set('queue.default', 'sync'); // Set intentionally for instant delivery of team chat
            Config::set('broadcasting.default', 'pusher'); // Set intentionally for instant delivery of team chat
        }

        // Call for Pusher
        event(new EventsTaskUpdated());

        if (\user()) {
            if (($movingTaskId != '' && $service job->id == $movingTaskId) || $movingTaskId == '') {
                $log = new AccountBaseController();
                $log->logTaskActivity($service job->id, user()->id, 'statusActivity', $service job->board_column_id);
            }
        }

        if ($service job->project_id) {

            if (($movingTaskId != '' && $service job->id == $movingTaskId) || $movingTaskId == '') {
                // Calculate site progress if enabled
                $this->calculateProjectProgress($service job->project_id);
                $this->calculateProjectProgressByTime($service job->project_id);
            }
        }
    }

    public function deleting(Service Job $service job)
    {
        $universalSearches = UniversalSearch::where('searchable_id', $service job->id)->where('module_type', 'service job')->get();

        if ($universalSearches) {
            foreach ($universalSearches as $universalSearch) {
                UniversalSearch::destroy($universalSearch->id);
            }
        }

        $notifyData = [
            'App\Notifications\NewTask',
            'App\Notifications\TaskUpdated',
            'App\Notifications\TaskComment',
            'App\Notifications\TaskCommentClient',
            'App\Notifications\TaskCompleted',
            'App\Notifications\TaskStatusUpdated',
            'App\Notifications\TaskApproval',
            'App\Notifications\NewClientTask',
            'App\Notifications\TaskCompletedClient',
            'App\Notifications\TaskNote',
            'App\Notifications\TaskNoteClient',
            'App\Notifications\TaskReminder',
            'App\Notifications\TaskUpdatedClient',
            'App\Notifications\SubTaskCreated',
            'App\Notifications\SubTaskCompleted'
        ];

        Notification::whereIn('type', $notifyData)
            ->whereNull('read_at')
            ->where(
                function ($q) use ($service job) {
                    $q->where('data', 'like', '{"id":' . $service job->id . ',%');
                    $q->orWhere('data', 'like', '%,"task_id":' . $service job->id . ',%');
                }
            )->delete();

        /* Start of deleting event from google calendar */
        $google = new Google();
        $googleAccount = company();

        if (company()->google_calendar_status == 'active' && $googleAccount->google_calendar_verification_status == 'verified' && $googleAccount->token) {
            $google->connectUsing($googleAccount->token);
            try {
                if ($service job->event_id) {
                    $google->service('Calendar')->events->delete('primary', $service job->event_id);
                }
            } catch (Exception $error) {
                if (is_null($error->getErrors())) {
                    // Delete google calendar connection data i.e. token, name, google_id
                    $googleAccount->name = null;
                    $googleAccount->token = null;
                    $googleAccount->google_id = null;
                    $googleAccount->google_calendar_verification_status = 'non_verified';
                    $googleAccount->save();
                }
            }
        }

        $service job->files()->each(function ($file) {
            Files::deleteFile($file->hashname, TaskFile::FILE_PATH);
            Files::deleteDirectory(TaskFile::FILE_PATH . '/' . $file->task_id);
            $file->delete();
        });

        /* End of deleting event from google calendar */
    }

    /**
     * @param Service Job $service job
     */
    public function deleted(Service Job $service job)
    {

        if (user()) {
            self::createEmployeeActivity(user()->id, 'service job-deleted');

        }

        if (!is_null($service job->project_id)) {
            info('task_completion 433435deleted');
            // Calculate site progress if enabled
            $this->calculateProjectProgress($service job->project_id);
            $this->calculateProjectProgressByTime($service job->project_id);
        }
    }

    protected function googleCalendarEvent($event)
    {
        $module = GoogleCalendarModule::first();
        $googleAccount = company();

        if (!company()) {
            return $event->event_id;
        }

        if (company()->google_calendar_status == 'active' && $googleAccount->google_calendar_verification_status == 'verified' && $googleAccount->token && $module->task_status == 1) {

            $google = new Google();
            $attendiesData = [];

            $attendees = TaskUser::with(['user'])->whereHas(
                'user', function ($query) {
                    $query->where('status', 'active')->where('google_calendar_status', true);
                }
            )->where('task_id', $event->id)->get();

            foreach ($attendees as $attend) {
                if (!is_null($attend->user) && !is_null($attend->user->email)) {
                    $attendiesData[] = ['email' => $attend->user->email];
                }
            }

            if ($event->start_date && $event->due_date) {
                $start_date = Carbon::parse($event->start_date)->shiftTimezone($googleAccount->timezone);
                $due_date = Carbon::parse($event->due_date)->shiftTimezone($googleAccount->timezone);

                // Create event
                $google = $google->connectUsing($googleAccount->token);

                $eventData = new Google_Service_Calendar_Event(
                    array(
                        'summary' => $event->heading,
                        'location' => $googleAccount->address,
                        'description' => $event->description,
                        'colorId' => 7,
                        'start' => array(
                            'dateTime' => $start_date,
                            'timeZone' => $googleAccount->timezone,
                        ),
                        'end' => array(
                            'dateTime' => $due_date,
                            'timeZone' => $googleAccount->timezone,
                        ),
                        'attendees' => $attendiesData,
                        'reminders' => array(
                            'useDefault' => false,
                            'overrides' => array(
                                array('method' => 'email', 'minutes' => 24 * 60),
                                array('method' => 'popup', 'minutes' => 10),
                            ),
                        ),
                    )
                );

                try {
                    if ($event->event_id) {
                        $results = $google->service('Calendar')->events->patch('primary', $event->event_id, $eventData);
                    }
                    else {
                        $results = $google->service('Calendar')->events->insert('primary', $eventData);
                    }

                    return $results->id;
                } catch (Exception $error) {
                    if (is_null($error->getErrors())) {
                        // Delete google calendar connection data i.e. token, name, google_id
                        $googleAccount->name = null;
                        $googleAccount->token = null;
                        $googleAccount->google_id = null;
                        $googleAccount->google_calendar_verification_status = 'non_verified';
                        $googleAccount->save();
                    }
                }
            }
        }

        return $event->event_id;
    }

    // Google calendar for multiple events
    protected function googleCalendarEventMulti($eventIds)
    {
        $googleAccount = company();

        if (company()->google_calendar_status == 'active' && $googleAccount->google_calendar_verification_status == 'verified' && $googleAccount->token) {
            $google = new Google();
            $events = Service Job::whereIn('id', $eventIds)->get();
            $event = $events->first();

            $frq = ['day' => 'DAILY', 'week' => 'WEEKLY', 'month', 'MONTHLY', 'year' => 'YEARLY'];
            $frequency = $frq[$event->repeat_type];

            $eventData = new Google_Service_Calendar_Event();
            $eventData->setSummary($event->heading);
            $eventData->setLocation('');

            $start = new Google_Service_Calendar_EventDateTime();
            $start->setDateTime($event->start_date->toAtomString());
            $start->setTimeZone($googleAccount->timezone);

            $eventData->setStart($start);
            $end = new Google_Service_Calendar_EventDateTime();
            $end->setDateTime($event->due_date->toAtomString());
            $end->setTimeZone($googleAccount->timezone);

            $eventData->setEnd($end);
            /** @phpstan-ignore-next-line */
            $eventData->setRecurrence(array('RRULE:FREQ=' . $frequency . ';INTERVAL=' . $event->repeat_every . ';COUNT=' . $event->repeat_cycles . ';'));

            $attendees = TaskUser::with(['user'])->whereHas(
                'user', function ($query) {
                    $query->where('status', 'active')->where('google_calendar_status', true);
                }
            )->where('task_id', $event->id)->get();

            $attendiesData = [];

            foreach ($attendees as $attend) {
                if (!is_null($attend->user) && !is_null($attend->user->email)) {
                    $attendee1 = new Google_Service_Calendar_EventAttendee();
                    $attendee1->setEmail($attend->user->email);
                    $attendiesData[] = $attendee1;
                }
            }

            /** @phpstan-ignore-next-line */
            $eventData->attendees = $attendiesData;

            // Create event
            $google->connectUsing($googleAccount->token);

            try {
                if ($event->event_id) {
                    $results = $google->service('Calendar')->events->patch('primary', $event->event_id, $eventData);
                }
                else {
                    $results = $google->service('Calendar')->events->insert('primary', $eventData);
                }

                foreach ($events as $event) {
                    $event->event_id = $results->id;
                    $event->save();
                }

                return;
            } catch (Exception $error) {
                if (is_null($error->getErrors())) {
                    // Delete google calendar connection data i.e. token, name, google_id
                    $googleAccount->name = null;
                    $googleAccount->token = null;
                    $googleAccount->google_id = null;
                    $googleAccount->google_calendar_verification_status = 'non_verified';
                    $googleAccount->save();
                }
            }

            foreach ($events as $event) {
                $event->event_id = $event->event_id;
                $event->save();
            }

        }
    }

}
