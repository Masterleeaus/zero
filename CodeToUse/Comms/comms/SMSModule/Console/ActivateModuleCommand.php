<?php

namespace Modules\SMSModule\Console;

use Illuminate\Console\Command;

class ActivateModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * You can run: php artisan module:activate SMSModule
     */
    protected $signature = 'module:activate {module_slug?}';

    /**
     * The console command description.
     */
    protected $description = 'Activate SMSModule module (Worksuite-compatible stub).';

    public function handle(): int
    {
        $this->info('SMSModule: activation stub complete.');
        return self::SUCCESS;
    }
}
