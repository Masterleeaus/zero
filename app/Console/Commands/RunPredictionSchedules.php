<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Predict\PredictionSchedule;
use App\Services\Predict\TitanPredictService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunPredictionSchedules extends Command
{
    protected $signature = 'predict:run-schedules
                            {--company= : Only run for a specific company ID}
                            {--type=    : Only run for a specific prediction type}';

    protected $description = 'Execute due prediction schedules for all active companies';

    public function handle(TitanPredictService $predictService): int
    {
        $companyFilter = $this->option('company') ? [(int) $this->option('company')] : null;
        $typeFilter    = $this->option('type');

        $query = PredictionSchedule::withoutGlobalScope('company')
            ->due()
            ->when($companyFilter !== null, static fn ($q) => $q->whereIn('company_id', $companyFilter))
            ->when($typeFilter, static fn ($q) => $q->where('prediction_type', $typeFilter));

        $schedules = $query->get();

        if ($schedules->isEmpty()) {
            $this->info('No prediction schedules due.');
            return self::SUCCESS;
        }

        $ran   = 0;
        $failed = 0;

        foreach ($schedules as $schedule) {
            try {
                $this->runSchedule($schedule, $predictService);
                $schedule->advanceNextRun();
                $ran++;
                $this->line("  ✓ company={$schedule->company_id} type={$schedule->prediction_type}");
            } catch (\Throwable $th) {
                $failed++;
                Log::error('RunPredictionSchedules: ' . $th->getMessage(), [
                    'company_id'      => $schedule->company_id,
                    'prediction_type' => $schedule->prediction_type,
                ]);
                $this->error("  ✗ company={$schedule->company_id} type={$schedule->prediction_type}: " . $th->getMessage());
            }
        }

        $this->info("Done. Ran: {$ran}, Failed: {$failed}");

        return self::SUCCESS;
    }

    private function runSchedule(PredictionSchedule $schedule, TitanPredictService $predictService): void
    {
        $companyId = $schedule->company_id;

        match ($schedule->prediction_type) {
            'demand_surge'  => $predictService->generateDemandForecast($companyId, now()),
            'capacity_gap'  => $predictService->generateCapacityGapPrediction($companyId, now()->addDay()),
            default         => $this->line("  - Skipping schedule-level run for type={$schedule->prediction_type} (requires subject context)"),
        };
    }
}
