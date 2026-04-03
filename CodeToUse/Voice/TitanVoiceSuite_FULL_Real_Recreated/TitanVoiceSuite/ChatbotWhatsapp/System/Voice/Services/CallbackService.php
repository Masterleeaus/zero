<?php

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Services;

use App\Extensions\ChatbotWhatsapp\System\Models\CallbackSchedule;
use Carbon\Carbon;

class CallbackService
{
    public function schedule(string $phoneNumber, ?Carbon $scheduledAt = null, array $metadata = []): CallbackSchedule
    {
        $scheduledAt ??= now()->addHour();

        return CallbackSchedule::query()->create([
            'phone_number' => $phoneNumber,
            'scheduled_at' => $scheduledAt,
            'status' => 'scheduled',
            'retry_count' => 0,
            'metadata' => $metadata,
        ]);
    }

    public function defaultSlot(): Carbon
    {
        return now()->addHour()->startOfHour();
    }
}
