<?php

namespace Modules\Timesheet\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Timesheet\Entities\Timesheet;
use Modules\Timesheet\Entities\TimesheetSubmission;
use Modules\Timesheet\Entities\TimesheetSubmissionItem;
use Modules\Timesheet\Events\TimesheetSubmitted;
use Modules\Timesheet\Events\TimesheetReviewed;
use Modules\Timesheet\Http\Requests\ApproveSubmissionRequest;
use Modules\Timesheet\Http\Requests\SubmitSubmissionRequest;
use Modules\Timesheet\Services\WeekService;

class TimesheetApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function myWeek(Request $request, WeekService $weeks)
    {
        abort_unless(Auth::check(), 403);
        if (!config('timesheet.features.approvals_enabled', true)) {
            return redirect()->route('timesheet.index')->with('error', __('Timesheet::timesheet.approvals.disabled'));
        }

        $userId = Auth::id();
        $date = $request->filled('date') ? Carbon::parse($request->input('date')) : Carbon::now();
        $weekStart = $weeks->weekStart($date)->toDateString();
        $weekEnd = $weeks->weekEnd($date)->toDateString();

        $submission = TimesheetSubmission::query()
            ->where('user_id', $userId)
            ->where('week_start', $weekStart)
            ->first();

        $entries = Timesheet::query()
            ->where('user_id', $userId)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->orderBy('date')
            ->get();

        return view('timesheet::approvals.my_week', compact('weekStart', 'weekEnd', 'submission', 'entries'));
    }

    public function submit(SubmitSubmissionRequest $request, WeekService $weeks)
    {
        abort_unless(Auth::check(), 403);
        $this->authorizePermission('timesheet submit');

        $userId = Auth::id();
        $date = Carbon::parse($request->input('date', Carbon::now()->toDateString()));
        $weekStart = $weeks->weekStart($date)->toDateString();
        $weekEnd = $weeks->weekEnd($date)->toDateString();

        $companyId = function_exists('company') && company() ? company()->id : null;
        $submissionId = null;

        DB::transaction(function () use ($userId, $weekStart, $weekEnd, $request, $companyId, &$submissionId) {
            $submission = TimesheetSubmission::query()->firstOrCreate(
                ['user_id' => $userId, 'week_start' => $weekStart],
                [
                    'company_id' => $companyId,
                    'workspace_id' => function_exists('getActiveWorkSpace') ? getActiveWorkSpace() : null,
                    'week_end' => $weekEnd,
                    'status' => 'draft',
                    'created_by' => function_exists('creatorId') ? creatorId() : $userId,
                ]
            );

            $submission->week_end = $weekEnd;
            $submission->status = 'submitted';
            $submission->submitted_at = now();
            $submission->submitted_by = $userId;
            $submission->submitter_notes = $request->input('submitter_notes');
            $submission->save();
            $submissionId = $submission->id;

            // Attach all entries in that week
            $entries = Timesheet::query()
                ->where('user_id', $userId)
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->pluck('id')
                ->all();

            TimesheetSubmissionItem::query()->where('submission_id', $submission->id)->delete();
            foreach ($entries as $id) {
                TimesheetSubmissionItem::create(['submission_id' => $submission->id, 'timesheet_id' => $id]);
            }
        });

        if ($submissionId) {
            $submission = TimesheetSubmission::find($submissionId);
            if ($submission) {
                event(new TimesheetSubmitted($submission));
            }
        }

        return redirect()->route('timesheet.approvals.my_week')->with('success', __('Timesheet::timesheet.approvals.submitted'));
    }

    public function inbox(Request $request)
    {
        abort_unless(Auth::check(), 403);
        $this->authorizePermission('timesheet approve');

        $pending = TimesheetSubmission::query()
            ->where('status', 'submitted')
            ->orderBy('submitted_at', 'desc')
            ->paginate(20);

        return view('timesheet::approvals.inbox', compact('pending'));
    }

    public function show(TimesheetSubmission $submission)
    {
        abort_unless(Auth::check(), 403);
        $this->authorizePermission('timesheet approve');

        $submission->load('items.timesheet');

        return view('timesheet::approvals.show', compact('submission'));
    }

    public function approve(ApproveSubmissionRequest $request, TimesheetSubmission $submission)
    {
        abort_unless(Auth::check(), 403);
        $this->authorizePermission('timesheet approve');

        if ($submission->status !== 'submitted') {
            return back()->with('error', __('Timesheet::timesheet.approvals.not_submitted'));
        }

        $submission->status = 'approved';
        $submission->approved_at = now();
        $submission->approved_by = Auth::id();
        $submission->approver_notes = $request->input('approver_notes');
        $submission->save();
            $submissionId = $submission->id;

        event(new TimesheetReviewed($submission, 'approved'));

        return redirect()->route('timesheet.approvals.inbox')->with('success', __('Timesheet::timesheet.approvals.approved'));
    }

    public function reject(ApproveSubmissionRequest $request, TimesheetSubmission $submission)
    {
        abort_unless(Auth::check(), 403);
        $this->authorizePermission('timesheet approve');

        if ($submission->status !== 'submitted') {
            return back()->with('error', __('Timesheet::timesheet.approvals.not_submitted'));
        }

        $submission->status = 'rejected';
        $submission->approved_at = now();
        $submission->approved_by = Auth::id();
        $submission->approver_notes = $request->input('approver_notes');
        $submission->save();
            $submissionId = $submission->id;

        event(new TimesheetReviewed($submission, 'rejected'));

        return redirect()->route('timesheet.approvals.inbox')->with('success', __('Timesheet::timesheet.approvals.rejected'));
    }

    private function authorizePermission(string $permission): void
    {
        // Worksuite uses isAbleTo() helper on user
        $user = Auth::user();
        if (method_exists($user, 'isAbleTo') && !$user->isAbleTo($permission)) {
            abort(403);
        }
    }
}
