<?php

namespace Modules\TitanHello\Services\Callbacks;

use Modules\TitanHello\Models\Call;
use Modules\TitanHello\Models\CallbackRequest;

class CallbackService
{
    public function createFromMissedCall(Call $call, array $meta = []): ?CallbackRequest
    {
        if (!$call->company_id) return null;

        // Avoid duplicates
        $existing = CallbackRequest::query()
            ->where('company_id', $call->company_id)
            ->where('call_id', $call->id)
            ->where('status', 'open')
            ->first();

        if ($existing) return $existing;

        $dueAt = $meta['due_at'] ?? now()->addMinutes(5);

        return CallbackRequest::create([
            'company_id' => $call->company_id,
            'call_id' => $call->id,
            'from_number' => $call->from_number,
            'to_number' => $call->to_number,
            'assigned_to' => $meta['assigned_to'] ?? null,
            'status' => 'open',
            'priority' => $meta['priority'] ?? 'normal',
            'due_at' => $dueAt,
            'note' => $meta['note'] ?? null,
            'created_by' => $meta['created_by'] ?? null,
        ]);
    }

    public function markDone(CallbackRequest $cb, ?int $userId = null): CallbackRequest
    {
        $cb->status = 'done';
        $cb->closed_at = now();
        if ($userId) {
            $cb->created_by = $cb->created_by ?: $userId;
        }
        $cb->save();
        return $cb;
    }

    public function cancel(CallbackRequest $cb): CallbackRequest
    {
        $cb->status = 'cancelled';
        $cb->closed_at = now();
        $cb->save();
        return $cb;
    }
}
