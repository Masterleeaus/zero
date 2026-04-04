<?php

declare(strict_types=1);

namespace App\Services\HRM;

use App\Models\Work\Attendance;
use App\Models\Work\BiometricPunch;

class BiometricIngestService
{
    public function ingestPunch(array $data): BiometricPunch
    {
        $punch = BiometricPunch::create($data);

        if ($punch->punch_type === 'clock_in') {
            $attendance = $this->resolveAttendance($punch);
            if ($attendance) {
                $punch->attendance_id = $attendance->id;
                $punch->saveQuietly();
            }
        } elseif ($punch->punch_type === 'clock_out') {
            $this->resolveAttendance($punch);
        }

        return $punch->refresh();
    }

    public function resolveAttendance(BiometricPunch $punch): ?Attendance
    {
        if ($punch->punch_type === 'clock_in') {
            $attendance = Attendance::create([
                'company_id'  => $punch->company_id,
                'user_id'     => $punch->user_id,
                'check_in_at' => $punch->punched_at,
                'status'      => 'checked_in',
            ]);

            $punch->attendance_id = $attendance->id;
            $punch->saveQuietly();

            return $attendance;
        }

        if ($punch->punch_type === 'clock_out') {
            $attendance = Attendance::query()
                ->where('company_id', $punch->company_id)
                ->where('user_id', $punch->user_id)
                ->where('status', 'checked_in')
                ->whereNull('check_out_at')
                ->latest('check_in_at')
                ->first();

            if ($attendance) {
                $attendance->update(['check_out_at' => $punch->punched_at]);
                $punch->attendance_id = $attendance->id;
                $punch->saveQuietly();
            }

            return $attendance;
        }

        return null;
    }
}
