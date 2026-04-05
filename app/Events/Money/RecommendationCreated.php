<?php
declare(strict_types=1);
namespace App\Events\Money;
use App\Models\Money\FinancialActionRecommendation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class RecommendationCreated {
    use Dispatchable, SerializesModels;
    public function __construct(public readonly FinancialActionRecommendation $recommendation) {}
}
