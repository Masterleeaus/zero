<?php

namespace Modules\Timesheet\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Timesheet\Entities\Timesheet;
use Modules\Timesheet\Entities\TimesheetTimer;
use Modules\Timesheet\Events\TimerStarted;
use Modules\Timesheet\Events\TimerStopped;
use Modules\Timesheet\Http\Requests\StartTimerRequest;
use Modules\Timesheet\Http\Requests\StopTimerRequest;
use Modules\Timesheet\Services\CompanySettings;
use Modules\Timesheet\Services\CostCalculator;
use Modules\Timesheet\Services\TimesheetIntegrationResolver;

class TimesheetTimerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        abort_unless(Auth::check(), 403);
        $user = Auth::user();
        if (method_exists($user, 'isAbleTo') && !$user->isAbleTo('timesheet timer')) {
            abort(403);
        }

        if (!config('timesheet.features.timer_enabled', true)) {
            return redirect()->route('timesheet.index')->with('error', __('Timesheet::timesheet.timer.disabled'));
        }

        $userId = Auth::id();

        $running = TimesheetTimer::query()
            ->where('user_id', $userId)
            ->where('status', 'running')
            ->orderByDesc('started_at')
            ->first();

        $recent = TimesheetTimer::query()
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return view('timesheet::timer.index', compact('running', 'recent'));
    }

    public function start(StartTimerRequest $request, TimesheetIntegrationResolver $resolver): RedirectResponse
    {
        abort_unless(Auth::check(), 403);
        $user = Auth::user();
        if (method_exists($user, 'isAbleTo') && !$user->isAbleTo('timesheet timer')) {
            abort(403);
        }
        if (!config('timesheet.features.timer_enabled', true)) {
            return back()->with('error', __('Timesheet::timesheet.timer.disabled'));
        }

        $userId = Auth::id();

        // Stop any existing running timer
        TimesheetTimer::query()
            ->where('user_id', $userId)
            ->where('status', 'running')
            ->update([
                'status' => 'stopped',
                'stopped_at' => now(),
            ]);

        $data = $request->validated();

        $timer = TimesheetTimer::create([
            'company_id' => function_exists('company') && company() ? company()->id : null,
            'workspace_id' => function_exists('getActiveWorkSpace') ? getActiveWorkSpace() : null,
            'user_id' => $userId,
            'project_id' => $data['project_id'] ?? null,
            'task_id' => $data['task_id'] ?? null,
            'work_order_id' => $data['work_order_id'] ?? null,
            'status' => 'running',
            'started_at' => now(),
            'notes' => $data['notes'] ?? null,
            'created_by' => function_exists('creatorId') ? creatorId() : $userId,
        ]);

        event(new TimerStarted($timer));

        return redirect()->route('timesheet.timer.index')->with('success', __('Timesheet::timesheet.timer.started'));
    }

    public function stop(StopTimerRequest $request, TimesheetIntegrationResolver $resolver, CostCalculator $calculator, CompanySettings $settings): RedirectResponse
    {
        abort_unless(Auth::check(), 403);
        $user = Auth::user();
        if (method_exists($user, 'isAbleTo') && !$user->isAbleTo('timesheet timer')) {
            abort(403);
        }
        if (!config('timesheet.features.timer_enabled', true)) {
            return back()->with('error', __('Timesheet::timesheet.timer.disabled'));
        }

        $userId = Auth::id();

        $timer = TimesheetTimer::query()
            ->where('user_id', $userId)
            ->where('status', 'running')
            ->orderByDesc('started_at')
            ->first();

        if (!$timer) {
            return back()->with('error', __('Timesheet::timesheet.timer.none_running'));
        }

        $now = Carbon::now();
        $seconds = max(0, $now->diffInSeconds(Carbon::parse($timer->started_at)));

        DB::transaction(function () use ($timer, $seconds, $request, $resolver, $calculator, $settings, $userId) {
            $timer->status = 'stopped';
            $timer->stopped_at = now();
            $timer->seconds_total = $seconds;
            $timer->save();

            $convert = $request->boolean('convert', true);
            if (!$convert) {
                return;
            }

            $minutesTotal = (int) floor($seconds / 60);
            $hours = (int) floor($minutesTotal / 60);
            $minutes = (int) ($minutesTotal % 60);

            $companyId = function_exists('company') && company() ? company()->id : null;

            $t = new Timesheet();
            $t->company_id = $companyId;
            $t->workspace_id = function_exists('getActiveWorkSpace') ? getActiveWorkSpace() : null;
            $t->created_by = function_exists('creatorId') ? creatorId() : $userId;
            $t->user_id = $userId;
            $t->project_id = $timer->project_id;
            $t->task_id = $timer->task_id;
            $t->work_order_id = $timer->work_order_id;
            $t->date = now()->toDateString();
            $t->hours = $hours;
            $t->minutes = $minutes;
            $t->type = $request->input('type', 'regular');
            $t->notes = $timer->notes;

            // Rate + multiplier via resolver/HR
            $hr = $resolver->hrm();
            $rate = $hr->getRatePerHour($t->user_id, $companyId);
            if ($rate !== null) {
                $t->fsm_rate_per_hour = $rate;
            }
            $t->fsm_overtime_multiplier = (float) config('timesheet.integrations.default_overtime_multiplier', 1.0);
            $t->fsm_cost_total = $calculator->computeCostTotal($t);

            $t->save();

            $timer->status = 'converted';
            $timer->save();
        });

        event(new TimerStopped($timer->fresh()));

        return redirect()->route('timesheet.index')->with('success', __('Timesheet::timesheet.timer.stopped_converted'));
    }
}
