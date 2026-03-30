<?php

namespace Modules\SiteInspection\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \Modules\Inspection\Entities\Schedule::class => \Modules\Inspection\Policies\SchedulePolicy::class,
        \Modules\Inspection\Entities\RecurringSchedule::class => \Modules\Inspection\Policies\RecurringSchedulePolicy::class,
        \Modules\Inspection\Entities\ScheduleFile::class => \Modules\Inspection\Policies\ScheduleFilePolicy::class,
        \Modules\Inspection\Entities\ScheduleReply::class => \Modules\Inspection\Policies\ScheduleReplyPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
