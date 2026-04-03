<?php

declare(strict_types=1);

namespace App\Events\Money;

use App\Models\Money\Expense;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExpenseApproved
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Expense $expense)
    {
    }
}
