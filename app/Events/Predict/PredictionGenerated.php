<?php

declare(strict_types=1);

namespace App\Events\Predict;

use App\Models\Predict\Prediction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PredictionGenerated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly Prediction $prediction) {}
}
