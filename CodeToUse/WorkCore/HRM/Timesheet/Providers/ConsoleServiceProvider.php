<?php

namespace Modules\Timesheet\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Timesheet\Console\TimesheetDoctorCommand;

class ConsoleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                TimesheetDoctorCommand::class,
            ]);
        }
    }
}
