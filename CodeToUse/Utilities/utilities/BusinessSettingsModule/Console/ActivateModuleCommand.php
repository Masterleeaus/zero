<?php

namespace Modules\BusinessSettingsModule\Console;

use Illuminate\Console\Command;

class ActivateModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * You can run: php artisan module:activate BusinessSettingsModule
     */
    protected $signature = 'module:activate {module_slug?}';

    /**
     * The console command description.
     */
    protected $description = 'Activate BusinessSettingsModule module (Worksuite-compatible stub).';

    public function handle(): int
    {
        $this->info('BusinessSettingsModule: activation stub complete.');
        return self::SUCCESS;
    }
}
