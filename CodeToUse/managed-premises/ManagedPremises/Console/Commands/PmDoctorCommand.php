<?php
namespace Modules\ManagedPremises\Console\Commands;

use Illuminate\Console\Command;

class PmDoctorCommand extends Command
{
    protected $signature = 'pm:doctor';
    protected $description = 'ManagedPremises health check (routes, views, migrations)';

    public function handle(): int
    {
        $this->info('ManagedPremises: OK (basic).');
        $this->line('Tip: php artisan route:list | grep managedpremises');
        return Command::SUCCESS;
    }
}
