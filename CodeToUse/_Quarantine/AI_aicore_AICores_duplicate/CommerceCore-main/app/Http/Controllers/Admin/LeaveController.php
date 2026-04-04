<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index()
    {
        $leaves = LeaveRequest::with('user')->latest()->paginate(20);
        return view('admin.leaves.index', compact('leaves'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string'
        ]);

        LeaveRequest::create(array_merge($validated, [
            'user_id' => auth()->id(),
            'status' => 'pending'
        ]));

        return back()->with('success', 'Leave request submitted successfully.');
    }

    public function update(Request $request, LeaveRequest $leave)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        $leave->update([
            'status' => $validated['status'],
            'approved_by' => auth()->id()
        ]);

        return back()->with('success', 'Leave request updated.');
    }
}
