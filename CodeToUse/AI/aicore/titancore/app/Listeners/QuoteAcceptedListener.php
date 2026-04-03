<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\QuoteAccepted;
use App\Notifications\LiveNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class QuoteAcceptedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function handle(QuoteAccepted $event): void
    {
        $quote = $event->quote;

        try {
            $owner = User::query()->find($quote->created_by);

            if ($owner) {
                $owner->notify(new LiveNotification(
                    message: __('Quote #:number has been accepted.', ['number' => $quote->quote_number]),
                    link: route('dashboard.money.quotes.show', $quote),
                    title: __('Quote Accepted'),
                ));
            }
        } catch (\Throwable $th) {
            Log::error('QuoteAcceptedListener: ' . $th->getMessage(), ['quote_id' => $quote->id]);
        }
    }
}
