<?php

declare(strict_types=1);

namespace App\Listeners\Predict;

use App\Events\Predict\HighConfidencePrediction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyOnHighConfidencePrediction implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function handle(HighConfidencePrediction $event): void
    {
        try {
            $prediction = $event->prediction;

            Log::channel('stack')->info('HighConfidencePrediction', [
                'prediction_id'    => $prediction->id,
                'prediction_type'  => $prediction->prediction_type,
                'confidence_score' => $prediction->confidence_score,
                'company_id'       => $prediction->company_id,
                'subject_type'     => $prediction->subject_type,
                'subject_id'       => $prediction->subject_id,
                'action'           => $prediction->recommended_action,
            ]);

            // Notification hooks — extend here to deliver in-app, email, or signal alerts.
        } catch (\Throwable $th) {
            Log::error('NotifyOnHighConfidencePrediction: ' . $th->getMessage());
        }
    }
}
