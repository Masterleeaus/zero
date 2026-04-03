<?php

namespace Modules\ChattingModule\Console;

use Illuminate\Console\Command;

class ActivateModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * You can run: php artisan module:activate ChattingModule
     */
    protected $signature = 'module:activate {module_slug?}';

    /**
     * The console command description.
     */
    protected $description = 'Activate ChattingModule module (Worksuite-compatible stub).';

    public function handle(): int
    {
        $this->info('ChattingModule: activation stub complete.');
        return self::SUCCESS;
    }
}
