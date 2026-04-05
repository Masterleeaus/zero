<?php
declare(strict_types=1);
namespace App\Events\Money;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class LiquidityRiskDetected {
    use Dispatchable, SerializesModels;
    public function __construct(public readonly int $companyId, public readonly array $detail) {}
}
