<?php

declare(strict_types=1);

namespace App\Console\Commands\Money;

use App\Models\Money\FinancialAsset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * RunDepreciationCommand — applies monthly depreciation to all active financial assets.
 *
 * Intended to run via scheduler on the 1st of each month:
 *   $schedule->command('money:depreciate')->monthlyOn(1, '02:00');
 */
class RunDepreciationCommand extends Command
{
    protected $signature   = 'money:depreciate {--company-id= : Restrict to a specific company_id} {--dry-run : Preview without writing}';
    protected $description = 'Apply monthly straight-line depreciation to all active financial assets.';

    public function handle(): int
    {
        $dryRun    = (bool) $this->option('dry-run');
        $companyId = $this->option('company-id');

        $query = FinancialAsset::active();

        if ($companyId) {
            $query->where('company_id', (int) $companyId);
        }

        $assetCount = (clone $query)->count();

        $this->info(sprintf('Processing %d active asset(s)…', $assetCount));

        $processed = 0;

        $query->chunkById(100, function ($assets) use ($dryRun, &$processed): void {
            foreach ($assets as $asset) {
                $charge = $asset->monthlyDepreciationCharge();

                if ($charge <= 0) {
                    $this->line("  — {$asset->name}: no charge (rate = 0)");
                    continue;
                }

                $before = (float) $asset->current_value;
                $after  = max(0.0, $before - $charge);

                $this->line(sprintf(
                    '  → [%d] %s: %.2f → %.2f (charge: %.2f)',
                    $asset->id,
                    $asset->name,
                    $before,
                    $after,
                    $charge
                ));

                if (! $dryRun) {
                    $asset->applyDepreciation();
                    Log::info('money.depreciation', [
                        'asset_id'   => $asset->id,
                        'company_id' => $asset->company_id,
                        'before'     => $before,
                        'after'      => $after,
                        'charge'     => $charge,
                    ]);
                }

                $processed++;
            }
        });
        $this->info(sprintf('Done. %d asset(s) %s.', $processed, $dryRun ? 'previewed' : 'depreciated'));

        return self::SUCCESS;
    }
}
