<?php

declare(strict_types=1);

namespace App\Listeners\Team;

use App\Events\Team\CertificationExpired;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyOnCertificationExpiry implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function handle(CertificationExpired $event): void
    {
        $cert = $event->certification;

        try {
            // Notification stub — wire to a Notification class when the
            // notification infrastructure is in place for the team domain.
            Log::info('capability.cert_expired', [
                'user_id'              => $cert->user_id,
                'certification_name'   => $cert->certification_name,
                'expires_at'           => $cert->expires_at?->toDateString(),
            ]);
        } catch (\Throwable $th) {
            Log::error('NotifyOnCertificationExpiry: ' . $th->getMessage(), [
                'certification_id' => $cert->id,
            ]);
        }
    }
}
