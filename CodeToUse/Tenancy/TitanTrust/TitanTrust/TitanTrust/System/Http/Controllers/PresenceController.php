<?php

declare(strict_types=1);

namespace App\Extensions\TitanTrust\System\Http\Controllers;

use App\Extensions\TitanTrust\System\Services\AttendanceDeriver;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PresenceController extends Controller
{
    public function markArrived(Request $request)
    {
        $data = $request->validate([
            'job_id' => ['required','integer'],
        ]);

        $userId = (int) auth()->id();
        $companyId = (int) (auth()->user()->company_id ?? $userId);
        $jobId = (int) $data['job_id'];

        $attendance = AttendanceDeriver::getOrCreate($companyId, $userId, $jobId, $userId);
        $attendance->update([
            'arrived_at' => now(),
            'arrived_source' => 'manual',
        ]);

        AttendanceDeriver::refreshDerived($companyId, $userId, $jobId);

        return redirect()->route('dashboard.user.titan-trust.capture.index', $request->only(['job_id','incident_id']))
            ->with('success', 'Marked arrived.');
    }

    public function markLeaving(Request $request)
    {
        $data = $request->validate([
            'job_id' => ['required','integer'],
        ]);

        $userId = (int) auth()->id();
        $companyId = (int) (auth()->user()->company_id ?? $userId);
        $jobId = (int) $data['job_id'];

        $attendance = AttendanceDeriver::getOrCreate($companyId, $userId, $jobId, $userId);
        $attendance->update([
            'left_at' => now(),
            'left_source' => 'manual',
        ]);

        AttendanceDeriver::refreshDerived($companyId, $userId, $jobId);

        return redirect()->route('dashboard.user.titan-trust.capture.index', $request->only(['job_id','incident_id']))
            ->with('success', 'Marked leaving.');
    }
}
