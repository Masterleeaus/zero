<?php

namespace Modules\ServicemanModule\Console;

use Illuminate\Console\Command;

class ActivateModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * You can run: php artisan module:activate ServicemanModule
     */
    protected $signature = 'module:activate {module_slug?}';

    /**
     * The console command description.
     */
    protected $description = 'Activate ServicemanModule module (Worksuite-compatible stub).';

    public function handle(): int
    {
        $this->info('ServicemanModule: activation stub complete.');
        return self::SUCCESS;
    }
}
