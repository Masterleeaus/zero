<?php

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Services;

use App\Extensions\ChatbotWhatsapp\System\Models\BusinessHour;
use Carbon\Carbon;

class BusinessHoursService
{
    public function isOpen(?Carbon $at = null): bool
    {
        $at ??= now($this->timezone());
        $at = $at->copy()->timezone($this->timezone());
        $day = (int) $at->dayOfWeekIso;

        $record = BusinessHour::query()
            ->where('day_of_week', $day)
            ->first();

        if ($record?->is_holiday) {
            return false;
        }

        $default = $this->defaultHours($day);
        if (!$record && !$default) {
            return false;
        }

        $open = (int) ($record->opening_hour ?? $default['open'] ?? -1);
        $close = (int) ($record->closing_hour ?? $default['close'] ?? -1);

        if ($open < 0 || $close < 0) {
            return false;
        }

        $hour = ((int) $at->format('G')) + (((int) $at->format('i')) / 60);

        return $hour >= $open && $hour < $close;
    }

    public function nextOpening(?Carbon $from = null): Carbon
    {
        $from ??= now($this->timezone());
        $cursor = $from->copy()->timezone($this->timezone())->startOfHour();

        for ($i = 0; $i < 14; $i++) {
            $day = (int) $cursor->dayOfWeekIso;
            $default = $this->defaultHours($day);
            $record = BusinessHour::query()->where('day_of_week', $day)->first();

            if (($record && !$record->is_holiday && $record->opening_hour !== null) || $default) {
                $open = (int) ($record->opening_hour ?? $default['open'] ?? 9);
                $candidate = $cursor->copy()->setTime($open, 0);
                if ($candidate->greaterThan($from)) {
                    return $candidate;
                }
            }

            $cursor->addDay()->startOfDay();
        }

        return $from->copy()->addDay()->setTime(9, 0);
    }

    public function timezone(): string
    {
        return (string) config('unified-communication.business_hours.timezone', config('app.timezone', 'UTC'));
    }

    protected function defaultHours(int $day): ?array
    {
        $defaults = (array) config('unified-communication.business_hours.default', []);

        return match ($day) {
            1, 2, 3, 4, 5 => $defaults['Monday-Friday'] ?? ['open' => 9, 'close' => 17],
            6, 7 => $defaults['Saturday-Sunday'] ?? null,
            default => null,
        };
    }
}
