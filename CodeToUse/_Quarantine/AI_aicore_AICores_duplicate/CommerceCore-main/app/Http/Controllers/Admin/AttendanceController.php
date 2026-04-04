<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::with('user')->latest()->paginate(30);
        $todayAttendance = Attendance::where('user_id', auth()->id())
            ->where('date', now()->toDateString())
            ->first();

        return view('admin.attendance.index', compact('attendances', 'todayAttendance'));
    }

    public function clockIn(Request $request)
    {
        $exists = Attendance::where('user_id', auth()->id())
            ->where('date', now()->toDateString())
            ->exists();

        if ($exists) {
            return back()->with('error', 'Already clocked in for today.');
        }

        Attendance::create([
            'user_id' => auth()->id(),
            'date' => now()->toDateString(),
            'clock_in' => now()->toTimeString(),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Clocked in successfully at ' . now()->format('h:i A'));
    }

    public function clockOut(Request $request)
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('date', now()->toDateString())
            ->whereNull('clock_out')
            ->first();

        if (!$attendance) {
            return back()->with('error', 'No active clock-in found for today or already clocked out.');
        }

        $attendance->update([
            'clock_out' => now()->toTimeString()
        ]);

        return back()->with('success', 'Clocked out successfully at ' . now()->format('h:i A'));
    }
}
