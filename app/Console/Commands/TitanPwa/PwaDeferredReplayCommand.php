<?php

namespace App\Console\Commands\TitanPwa;

use App\Services\TitanZeroPwaSystem\PwaDeferredReplayService;
use App\Models\Company;
use Illuminate\Console\Command;

/**
 * Artisan command: pwa:replay-deferred
 *
 * Replays failed or deferred PWA ingress items eligible for retry.
 * Intended to run via Laravel scheduler (every 5 minutes).
 *
 * Options:
 *   --company=N   Limit replay to a single company
 *   --limit=N     Max items to replay per company (default 100)
 *   --dry-run     Print eligible counts without dispatching jobs
 *   --prune       Also prune dead-letter items older than 30 days
 */
class PwaDeferredReplayCommand extends Command
{
    protected $signature = 'pwa:replay-deferred
                            {--company= : Limit to a specific company ID}
                            {--limit=100 : Max items to replay per company}
                            {--dry-run : Print eligible counts without dispatching}
                            {--prune : Prune dead-letter items older than 30 days}';

    protected $description = 'Replay failed or deferred PWA ingress items eligible for retry';

    public function handle(PwaDeferredReplayService $replayService): int
    {
        $companyOption = $this->option('company');
        $limit         = (int) $this->option('limit');
        $dryRun        = (bool) $this->option('dry-run');
        $prune         = (bool) $this->option('prune');

        $companies = $companyOption
            ? Company::where('id', $companyOption)->get()
            : Company::all();

        if ($companies->isEmpty()) {
            $this->warn('No companies found.');
            return self::SUCCESS;
        }

        foreach ($companies as $company) {
            $companyId = (int) $company->id;

            if ($dryRun) {
                $summary = $replayService->deadLetterSummary($companyId);
                $this->line("Company {$companyId}: {$summary['count']} dead-letter items");
                continue;
            }

            $result = $replayService->replayForCompany($companyId, $limit);

            $this->line(sprintf(
                'Company %d: replayed=%d  skipped=%d  dead_letters=%d',
                $companyId,
                $result['replayed'],
                $result['skipped'],
                $result['dead_letter_total'],
            ));

            if ($prune) {
                $pruned = $replayService->pruneDeadLetters($companyId);
                $this->line("  → Pruned {$pruned} dead-letter items");
            }
        }

        return self::SUCCESS;
    }
}
