<?php

declare(strict_types=1);

namespace App\Listeners\Team;

use App\Events\Team\CapabilityGapDetected;
use App\Events\Team\CertificationExpired;
use App\Events\Team\CertificationRevoked;
use App\Events\Team\SkillAssigned;
use App\Titan\Signals\AuditTrail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class RecordCapabilityAuditTrail implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function __construct(private readonly AuditTrail $auditTrail) {}

    /**
     * Handle any of the four Capability events via a union-type hint.
     */
    public function handle(SkillAssigned|CertificationExpired|CertificationRevoked|CapabilityGapDetected $event): void
    {
        try {
            $entry = match (true) {
                $event instanceof SkillAssigned => [
                    'action'  => 'capability.skill_assigned',
                    'user_id' => $event->technicianSkill->user_id,
                    'detail'  => ['skill_definition_id' => $event->technicianSkill->skill_definition_id, 'level' => $event->technicianSkill->level],
                ],
                $event instanceof CertificationExpired => [
                    'action'  => 'capability.cert_expired',
                    'user_id' => $event->certification->user_id,
                    'detail'  => ['certification_name' => $event->certification->certification_name],
                ],
                $event instanceof CertificationRevoked => [
                    'action'  => 'capability.cert_revoked',
                    'user_id' => $event->certification->user_id,
                    'detail'  => ['certification_name' => $event->certification->certification_name],
                ],
                $event instanceof CapabilityGapDetected => [
                    'action'  => 'capability.gap_detected',
                    'user_id' => $event->user->id,
                    'detail'  => ['missing' => $event->missing, 'expired' => $event->expired],
                ],
                default => null,
            };

            if ($entry === null) {
                return;
            }

            $this->auditTrail->recordEntry(
                'capability_registry',
                $entry['action'],
                $entry['detail'],
                null,
                $entry['user_id'],
            );
        } catch (\Throwable $th) {
            Log::error('RecordCapabilityAuditTrail: ' . $th->getMessage());
        }
    }
}
