<?php

namespace Modules\Communication\Console;

use Illuminate\Console\Command;

class ActivateModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * You can run: php artisan module:activate Communication
     */
    protected $signature = 'module:activate {module_slug?}';

    /**
     * The console command description.
     */
    protected $description = 'Activate Communication module (Worksuite-compatible stub).';

    public function handle(): int
    {
        $this->info('Communication: activation stub complete.');
        return self::SUCCESS;
    }
}
