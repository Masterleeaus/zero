<?php

namespace Modules\TitanHello\Console\Commands;

use Illuminate\Console\Command;
use Modules\TitanHello\Jobs\PruneOldRecordingsJob;

class PruneRecordingsCommand extends Command
{
    protected $signature = 'titanhello:prune-recordings {--days= : Retention days override}';
    protected $description = 'Prune stored call recordings according to Titan Hello retention policy';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('titanhello.recordings.retention_days', 30));
        if ($days <= 0) {
            $this->error('Retention days must be > 0');
            return 1;
        }

        PruneOldRecordingsJob::dispatch($days);

        $this->info('Queued pruning job for recordings older than ' . $days . ' days.');
        return 0;
    }
}
