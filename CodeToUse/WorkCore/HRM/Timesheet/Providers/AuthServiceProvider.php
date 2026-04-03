<?php

namespace Modules\Timesheet\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\Timesheet\Entities\Timesheet;
use Modules\Timesheet\Entities\TimesheetSubmission;
use Modules\Timesheet\Policies\TimesheetPolicy;
use Modules\Timesheet\Policies\TimesheetSubmissionPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Timesheet::class => TimesheetPolicy::class,
        TimesheetSubmission::class => TimesheetSubmissionPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
