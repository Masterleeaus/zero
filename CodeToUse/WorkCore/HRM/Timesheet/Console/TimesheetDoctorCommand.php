<?php

namespace Modules\Timesheet\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class TimesheetDoctorCommand extends Command
{
    protected $signature = 'timesheet:doctor {--fix-company-id : Attempt to backfill company_id where missing}';
    protected $description = 'Validate Timesheet module schema & common misconfigurations';

    public function handle(): int
    {
        $this->info('Timesheet Doctor');
        $has = Schema::hasTable('timesheets');
        $this->line('timesheets table: ' . ($has ? 'OK' : 'MISSING'));
        if (!$has) {
            return self::FAILURE;
        }

        foreach (['user_id','date','hours','minutes','type','workspace_id','created_by'] as $col) {
            $this->line(sprintf('%s: %s', $col, Schema::hasColumn('timesheets', $col) ? 'OK' : 'MISSING'));
        }

        if ($this->option('fix-company-id') && Schema::hasColumn('timesheets','company_id')) {
            $this->warn('company_id backfill is environment-specific. Implement a custom backfill strategy in TimesheetDoctorCommand if needed.');
        }

        return self::SUCCESS;
    }
}
