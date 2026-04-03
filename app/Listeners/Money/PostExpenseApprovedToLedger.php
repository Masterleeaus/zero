<?php

declare(strict_types=1);

namespace App\Listeners\Money;

use App\Events\Money\ExpenseApproved;
use App\Services\TitanMoney\AccountingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class PostExpenseApprovedToLedger implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function __construct(private readonly AccountingService $accounting)
    {
    }

    public function handle(ExpenseApproved $event): void
    {
        try {
            $this->accounting->postExpenseApproved($event->expense);
        } catch (\Throwable $e) {
            Log::error('PostExpenseApprovedToLedger: ' . $e->getMessage(), [
                'expense_id' => $event->expense->id,
            ]);
        }
    }
}
