<?php

declare(strict_types=1);

namespace App\Listeners\Predict;

use App\Models\Work\ServiceAgreement;
use App\Services\Predict\TitanPredictService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdateSLAPredictionOnJobCompletion implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function __construct(private readonly TitanPredictService $predictService) {}

    public function handle(object $event): void
    {
        try {
            $agreementId = $event->job->service_agreement_id ?? null;

            if ($agreementId === null) {
                return;
            }

            /** @var ServiceAgreement|null $agreement */
            $agreement = ServiceAgreement::find($agreementId);

            if ($agreement === null) {
                return;
            }

            $this->predictService->generateSLARiskPrediction($agreement);
        } catch (\Throwable $th) {
            Log::error('UpdateSLAPredictionOnJobCompletion: ' . $th->getMessage());
        }
    }
}
