<?php

namespace App\Console\Commands;

use App\Events\ProjectReminderEvent;
use App\Models\Company;
use App\Models\Site;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class SendProjectReminder extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-site-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send site reminder to the admins before specified days of the site';


    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {

        Company::active()
            ->select([
                'companies.id as id',
                'project_settings.id as pid',
                'project_settings.*',
            ])
            ->join('project_settings', 'project_settings.company_id', '=', 'companies.id')
            ->where('send_reminder', 'yes')->chunk(50, function ($companies) {
                foreach ($companies as $company) {

                    $sites = Site::whereNotNull('deadline')
                        ->whereDate('deadline', now($company->timezone)->addDays($company->remind_time))
                        ->where('company_id', $company->id)
                        ->get()
                        ->makeHidden('isProjectAdmin');

                    if ($sites->count() == 0) {
                        continue;
                    }

                    $members = [];

                    foreach ($sites as $site) {
                        // Get site members
                        foreach ($site->members as $member) {
                            $members = Arr::add($members, $member->user->id, $member->user);
                        }
                    }

                    $members = collect(array_values($members));

                    $users = [];

                    if (in_array('admins', json_decode($company->remind_to)) && in_array('members', json_decode($company->remind_to))) {

                        $admins = User::allAdmins($company->id)->makeHidden('unreadNotifications');
                        $users = $admins->merge($members);

                    }
                    else {

                        if (in_array('admins', json_decode($company->remind_to))) {
                            $users = User::allAdmins($company->id)->makeHidden('unreadNotifications');
                        }

                        if (in_array('members', json_decode($company->remind_to))) {
                            $users = collect($users)->merge($members);
                        }
                    }

                    foreach ($users as $user) {
                        $projectsArr = [];

                        foreach ($user->member as $projectMember) {
                            $projectsArr = Arr::add($projectsArr, $projectMember->site->id, $projectMember->site->makeHidden('isProjectAdmin'));
                        }

                        $projectsArr = collect(array_values($projectsArr));

                        if (!$user->isAdmin($user->id)) {
                            $projectsArr = $this->filterProjects($projectsArr, $company);
                        }
                        else {
                            $projectsArr = !in_array('admins', json_decode($company->remind_to)) ? $this->filterProjects($projectsArr, $company) : $sites;
                        }

                        if ($projectsArr->count()) {
                            event(new ProjectReminderEvent($projectsArr, $user, ['company' => $company, 'project_setting' => $company]));
                        }

                    }
                }
            });

        return Command::SUCCESS;
    }

    public function filterProjects($projectsArr, $company)
    {
        return $projectsArr->filter(function ($site) use ($company) {
            return Carbon::parse($site->deadline, $company->timezone)->equalTo(now($company->timezone)->addDays($company->remind_time)->startOfDay());
        });
    }

}
