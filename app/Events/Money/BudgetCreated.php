<?php
declare(strict_types=1);
namespace App\Events\Money;
use App\Models\Money\Budget;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class BudgetCreated {
    use Dispatchable, SerializesModels;
    public function __construct(public readonly Budget $budget) {}
}
