<?php

namespace App\Titan\Signals;

use App\Titan\Signals\Subscribers\PulseSubscriber;
use App\Titan\Signals\Subscribers\RewindSubscriber;
use App\Titan\Signals\Subscribers\ZeroSubscriber;
use Illuminate\Support\Facades\DB;
use Throwable;

class SignalDispatcher
{
    /** @var SignalSubscriberInterface[] */
    protected array $subscribers;

    public function __construct(
        protected ?AuditTrail $auditTrail = null,
    ) {
        $this->auditTrail ??= app(AuditTrail::class);
        $this->subscribers = [
            app(ZeroSubscriber::class),
            app(PulseSubscriber::class),
            app(RewindSubscriber::class),
        ];
    }

    public function subscribers(): array
    {
        return $this->subscribers;
    }

    public function dispatch(array $signal): array
    {
        $results = [];
        $failed = false;
        $errors = [];

        foreach ($this->subscribers as $subscriber) {
            try {
                $result = $subscriber->handle($signal);
                $results[] = [
                    'subscriber' => $subscriber->name(),
                    'result' => $result,
                ];
            } catch (Throwable $e) {
                $failed = true;
                $errors[] = [
                    'subscriber' => $subscriber->name(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        if (! empty($signal['process_id'])) {
            $this->auditTrail->recordEntry(
                $signal['process_id'],
                $failed ? 'signal.dispatch_failed' : 'signal.dispatched',
                [
                    'subscribers' => array_column($results, 'subscriber'),
                    'errors' => $errors,
                ],
                $signal['id'] ?? null,
                $signal['user_id'] ?? null,
            );
        }

        DB::table('tz_signal_queue')->where('signal_id', $signal['id'])->update([
            'broadcast_status' => $failed ? 'failed' : 'dispatched',
            'broadcast_at' => now(),
            'last_error' => $failed ? json_encode($errors, JSON_UNESCAPED_UNICODE) : null,
            'retry_count' => DB::raw('retry_count + '.($failed ? 1 : 0)),
        ]);

        return [
            'ok' => ! $failed,
            'results' => $results,
            'errors' => $errors,
        ];
    }

    public function flushPending(int $limit = 50): array
    {
        $rows = DB::table('tz_signal_queue')
            ->whereIn('broadcast_status', ['pending', 'failed'])
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        $results = [];
        foreach ($rows as $row) {
            $signal = json_decode($row->payload ?? '[]', true) ?: [];
            if (! empty($signal['id'])) {
                $results[$signal['id']] = $this->dispatch($signal);
            }
        }

        return $results;
    }
}
