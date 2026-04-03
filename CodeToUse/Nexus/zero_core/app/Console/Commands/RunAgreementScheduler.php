<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\Work\AgreementSchedulerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunAgreementScheduler extends Command
{
    protected $signature = 'agreements:run-scheduled
                            {--company= : Only run for a specific company ID}';

    protected $description = 'Create service jobs for all due service agreements';

    public function handle(AgreementSchedulerService $scheduler): int
    {
        $companyIds = $this->option('company')
            ? [(int) $this->option('company')]
            : Company::query()->pluck('id')->all();

        $totalCreated = 0;
        $totalSkipped = 0;

        foreach ($companyIds as $companyId) {
            try {
                $result = $scheduler->runDueAgreements($companyId);
                $totalCreated += $result['created'];
                $totalSkipped += $result['skipped'];

                if ($result['processed'] > 0) {
                    $this->line("Company {$companyId}: processed={$result['processed']} created={$result['created']} skipped={$result['skipped']}");
                }
            } catch (\Throwable $th) {
                Log::error('RunAgreementScheduler: company ' . $companyId . ': ' . $th->getMessage());
                $this->error("Company {$companyId} failed: " . $th->getMessage());
            }
        }

        $this->info("Done. Jobs created: {$totalCreated}, skipped (duplicate): {$totalSkipped}");

        return self::SUCCESS;
    }
}
