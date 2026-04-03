<?php

namespace Modules\Communication\Services\Ai;

use Illuminate\Support\Facades\Log;

/**
 * Communication Action Handler (TitanZero / Automation)
 *
 * Safe-by-default:
 * - Does NOT assume specific SMS/Email providers are configured.
 * - Logs intended action and returns a structured result.
 * - Can be expanded later to call module repositories.
 */
class CommunicationActionHandler
{
    /**
     * @param string $actionType
     */
    public function accepts(string $actionType): bool
    {
        return in_array($actionType, [
            'send_delay_message',
            'send_followup_sms',
            'send_followup_email',
            'draft_message',
        ], true);
    }

    /**
     * Execute a communication action.
     *
     * Expected payload keys vary by action but commonly include:
     * - channel: sms|email
     * - template: string
     * - booking_id / customer_id / to
     * - message: string (for draft_message)
     */
    public function execute(string $actionType, array $payload = [], ?int $tenantId = null): array
    {
        if (!$this->accepts($actionType)) {
            return ['ok' => false, 'reason' => 'Unsupported action_type', 'action_type' => $actionType];
        }

        // For now, do not send anything automatically. Log + return.
        try {
            Log::info('AI Communication action queued/executed (safe log only)', [
                'action_type' => $actionType,
                'tenant_id' => $tenantId,
                'payload' => $payload,
                'module' => 'Communication',
            ]);

            return [
                'ok' => true,
                'mode' => 'log_only',
                'action_type' => $actionType,
                'tenant_id' => $tenantId,
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'reason' => $e->getMessage(), 'action_type' => $actionType];
        }
    }

protected function isLogOnly(): bool
{
    try {
        return (bool) (config('communication.ai.log_only', true));
    } catch (\Throwable $e) {
        return true;
    }
}
}
