<?php

namespace App\Extensions\TitanRewind\System\Console\Commands;

use App\Extensions\TitanRewind\System\Models\RewindCase;
use App\Extensions\TitanRewind\System\Services\RewindExternalTransactionService;
use App\Extensions\TitanRewind\System\Services\RewindFixService;
use App\Extensions\TitanRewind\System\Services\RewindNotificationService;
use Illuminate\Console\Command;

class ProcessRewindQueue extends Command
{
    protected $signature = 'titanrewind:process {--limit=50 : Maximum confirmed fixes to process}';
    protected $description = 'Process confirmed rewind fixes, flush queued notifications, and queue payment reversals for rolled-back payment cases.';

    public function handle(
        RewindFixService $fixes,
        RewindNotificationService $notifications,
        RewindExternalTransactionService $externalTransactions,
    ): int {
        $limit = (int) $this->option('limit');
        $processedFixes = $fixes->processQueue($limit);
        $dispatchedNotifications = $notifications->flushQueuedNotifications($limit * 2);

        $reversalQueued = 0;
        $cases = RewindCase::query()
            ->where('status', 'rolled-back')
            ->where('entity_type', 'payments')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        foreach ($cases as $case) {
            $reversalQueued += $externalTransactions->queueReversalActions($case, ['type' => 'system', 'id' => null]);
        }

        $this->info(sprintf('Processed fixes: %d | notifications: %d | payment reversals queued: %d', $processedFixes, $dispatchedNotifications, $reversalQueued));

        return self::SUCCESS;
    }
}
