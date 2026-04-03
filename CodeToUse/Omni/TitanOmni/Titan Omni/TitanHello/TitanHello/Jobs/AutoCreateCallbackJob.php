<?php

namespace Modules\TitanHello\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\TitanHello\Models\Call;
use Modules\TitanHello\Services\Callbacks\CallbackService;

class AutoCreateCallbackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $callId;

    public function __construct(int $callId)
    {
        $this->callId = $callId;
        $this->onQueue('titanhello');
    }

    public function handle(CallbackService $callbacks): void
    {
        $call = Call::find($this->callId);
        if (!$call) return;

        if (($call->call_outcome ?? '') !== 'missed') return;

        $callbacks->createFromMissedCall($call);
    }
}
