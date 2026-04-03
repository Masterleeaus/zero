<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\Work\ContractRenewalService;
use Illuminate\Console\Command;

class ProcessContractRenewals extends Command
{
    protected $signature = 'contracts:process-renewals
                            {--company= : Process a specific company ID only}
                            {--within-days=0 : Only renew contracts expiring within this many days (0 = expired/due now)}';

    protected $description = 'Process auto-renewable contracts and fire expiry/renewal events';

    public function __construct(private readonly ContractRenewalService $renewalService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $withinDays = (int) $this->option('within-days');
        $companyId  = $this->option('company');

        if ($companyId) {
            $this->processCompany((int) $companyId, $withinDays);

            return self::SUCCESS;
        }

        if (class_exists(Company::class)) {
            Company::query()->each(function ($company) use ($withinDays) {
                $this->processCompany($company->id, $withinDays);
            });
        } else {
            $this->warn('No Company model found — skipping multi-tenant renewal sweep.');
        }

        return self::SUCCESS;
    }

    private function processCompany(int $companyId, int $withinDays): void
    {
        $results = $this->renewalService->processAutoRenewals($companyId, $withinDays);

        $this->info(sprintf(
            '[Company %d] processed=%d, renewed=%d, errors=%d',
            $companyId,
            $results['processed'],
            $results['renewed'],
            count($results['errors'])
        ));

        foreach ($results['errors'] as $error) {
            $this->error(sprintf(
                '  Agreement %d: %s',
                $error['agreement_id'],
                $error['error']
            ));
        }
    }
}
