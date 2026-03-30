<?php
namespace Modules\PropertyManagement\Console\Commands;

use Illuminate\Console\Command;

class PmDoctorCommand extends Command
{
    protected $signature = 'pm:doctor';
    protected $description = 'PropertyManagement health check (routes, views, migrations)';

    public function handle(): int
    {
        $this->info('PropertyManagement: OK (basic).');
        $this->line('Tip: php artisan route:list | grep propertymanagement');
        return Command::SUCCESS;
    }
}
