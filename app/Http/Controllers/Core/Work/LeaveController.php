<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Work;

use App\Http\Controllers\Core\CoreController;
use App\Events\Work\LeaveApproved;
use App\Events\Work\LeaveRejected;
use App\Models\User;
use App\Models\Work\Leave;
use App\Models\Work\LeaveHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveController extends CoreController
{
    public function index(Request $request): View
    {
        $leaves = Leave::query()
            ->where('company_id', $request->user()->company_id)
            ->latest('start_date')
            ->paginate(20);

        return view('default.panel.user.work.leaves.index', compact('leaves'));
    }

    public function create(Request $request): View
    {
        $users = User::query()->where('company_id', $request->user()->company_id)->get();

        return view('default.panel.user.work.leaves.create', [
            'users' => $users,
            'types' => ['annual', 'sick', 'custom'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id'    => ['required', 'exists:users,id'],
            'type'       => ['required', 'string'],
            'status'     => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'reason'     => ['nullable', 'string'],
        ]);

        $user = User::query()->findOrFail($data['user_id']);
        abort_if($user->company_id !== $request->user()->company_id, 403);

        $data['company_id'] = $request->user()->company_id;
        $data['status'] = $data['status'] ?? 'approved';

        $leave = Leave::create($data);

        LeaveHistory::create([
            'company_id'   => $leave->company_id,
            'leave_id'     => $leave->id,
            'performed_by' => $request->user()->id,
            'action'       => 'created',
            'notes'        => $leave->reason,
        ]);

        return redirect()->route('dashboard.work.leaves.index')->with('message', __('Leave created'));
    }

    public function show(Request $request, Leave $leave): View
    {
        abort_if($leave->company_id !== $request->user()->company_id, 403);

        $leave->load(['user', 'histories.performedBy']);

        return view('default.panel.user.work.leaves.show', compact('leave'));
    }

    public function edit(Request $request, Leave $leave): View
    {
        abort_if($leave->company_id !== $request->user()->company_id, 403);

        $users = User::query()->where('company_id', $request->user()->company_id)->get();

        return view('default.panel.user.work.leaves.edit', [
            'leave' => $leave,
            'users' => $users,
            'types' => ['annual', 'sick', 'custom'],
        ]);
    }

    public function update(Request $request, Leave $leave): RedirectResponse
    {
        abort_if($leave->company_id !== $request->user()->company_id, 403);

        $data = $request->validate([
            'user_id'    => ['required', 'exists:users,id'],
            'type'       => ['required', 'string'],
            'status'     => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'reason'     => ['nullable', 'string'],
        ]);

        $user = User::query()->findOrFail($data['user_id']);
        abort_if($user->company_id !== $request->user()->company_id, 403);

        $data['company_id'] = $request->user()->company_id;

        $leave->update($data);

        LeaveHistory::create([
            'company_id'   => $leave->company_id,
            'leave_id'     => $leave->id,
            'performed_by' => $request->user()->id,
            'action'       => 'updated',
            'notes'        => $leave->reason,
        ]);

        return redirect()->route('dashboard.work.leaves.show', $leave)->with('message', __('Leave updated'));
    }

    public function destroy(Request $request, Leave $leave): RedirectResponse
    {
        abort_if($leave->company_id !== $request->user()->company_id, 403);

        $leave->delete();

        return redirect()->route('dashboard.work.leaves.index')->with('message', __('Leave deleted'));
    }

    public function approve(Request $request, Leave $leave): RedirectResponse
    {
        $this->authorize('approve', $leave);

        $leave->update([
            'status'      => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        LeaveApproved::dispatch($leave, $request->user());

        return redirect()->back()->with('success', 'Leave approved.');
    }

    public function reject(Request $request, Leave $leave): RedirectResponse
    {
        $this->authorize('reject', $leave);

        $reason = $request->input('reason', '');

        $leave->update([
            'status'           => 'rejected',
            'rejection_reason' => $reason,
        ]);

        LeaveRejected::dispatch($leave, $request->user(), $reason);

        return redirect()->back()->with('success', 'Leave rejected.');
    }
}
