<?php

namespace Modules\ZoneManagement\Console;

use Illuminate\Console\Command;

class ActivateModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * You can run: php artisan module:activate ZoneManagement
     */
    protected $signature = 'module:activate {module_slug?}';

    /**
     * The console command description.
     */
    protected $description = 'Activate ZoneManagement module (Worksuite-compatible stub).';

    public function handle(): int
    {
        $this->info('ZoneManagement: activation stub complete.');
        return self::SUCCESS;
    }
}
