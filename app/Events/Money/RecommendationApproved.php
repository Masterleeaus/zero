<?php
declare(strict_types=1);
namespace App\Events\Money;
use App\Models\Money\FinancialActionRecommendation;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class RecommendationApproved {
    use Dispatchable, SerializesModels;
    public function __construct(
        public readonly FinancialActionRecommendation $recommendation,
        public readonly User $reviewer,
    ) {}
}
