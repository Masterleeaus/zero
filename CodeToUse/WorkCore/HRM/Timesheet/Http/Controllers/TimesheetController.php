<?php

namespace Modules\Timesheet\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Timesheet\Entities\Timesheet;
use Modules\Timesheet\Events\CreateTimesheet;
use Modules\Timesheet\Events\DestroyTimesheet;
use Modules\Timesheet\Events\UpdateTimesheet;
use Modules\Timesheet\Http\Requests\StoreTimesheetRequest;
use Modules\Timesheet\Http\Requests\UpdateTimesheetRequest;
use Modules\Timesheet\Services\CompanySettings;
use Modules\Timesheet\Services\CostCalculator;
use Modules\Timesheet\Services\TimesheetIntegrationResolver;

class TimesheetController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index(Request $request)
    {
        $this->authorizePermission('timesheet manage');

        $query = Timesheet::query()->forCreator()->orderByDesc('date');

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        }

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->input('to'));
        }

        $timesheets = $query->paginate(25);

        return view('timesheet::timesheet.index', compact('timesheets'));
    }

    public function create(TimesheetIntegrationResolver $resolver)
    {
        $this->authorizePermission('timesheet create');

        $tasks = [];
        return view('timesheet::timesheet.create', compact('tasks'));
    }

    public function store(StoreTimesheetRequest $request, TimesheetIntegrationResolver $resolver, CostCalculator $calculator, CompanySettings $settings): RedirectResponse
    {
        $this->authorizePermission('timesheet create');

        $data = $request->validated();

        $companyId = function_exists('company') && company() ? company()->id : null;
        $userId = Auth::id();

        $t = new Timesheet();
        $t->company_id = $companyId;
        $t->workspace_id = function_exists('getActiveWorkSpace') ? getActiveWorkSpace() : null;
        $t->created_by = function_exists('creatorId') ? creatorId() : $userId;

        $t->user_id = (int) ($data['user_id'] ?? $userId);
        $t->project_id = $data['project_id'] ?? null;
        $t->task_id = $data['task_id'] ?? null;
        $t->work_order_id = $data['work_order_id'] ?? null;
        $t->date = $data['date'];
        $t->hours = (int) ($data['hours'] ?? 0);
        $t->minutes = (int) ($data['minutes'] ?? 0);
        $t->type = $data['type'] ?? 'regular';
        $t->notes = $data['notes'] ?? null;

        // Rate + cost
        $hr = $resolver->hrm();
        $rate = $hr->getRatePerHour($t->user_id, $companyId);
        if ($rate !== null) {
            $t->fsm_rate_per_hour = $rate;
        }
        $t->fsm_overtime_multiplier = (float) config('timesheet.integrations.default_overtime_multiplier', 1.0);

        // Per-company overrides
        if ($companyId) {
            if ($settings->bool($companyId, 'costing_enabled', config('timesheet.features.costing_enabled', true)) === false) {
                $t->fsm_cost_total = null;
            } else {
                $t->fsm_cost_total = $calculator->computeCostTotal($t);
            }
        } else {
            $t->fsm_cost_total = $calculator->computeCostTotal($t);
        }

        $t->save();

        event(new CreateTimesheet($t));

        return redirect()->route('timesheet.index')->with('success', __('Timesheet::timesheet.msg.created'));
    }

    public function edit(Timesheet $timesheet, TimesheetIntegrationResolver $resolver)
    {
        $this->authorizePermission('timesheet edit');

        abort_unless($timesheet->created_by == (function_exists('creatorId') ? creatorId() : $timesheet->created_by), 403);

        $tasks = [];
        return view('timesheet::timesheet.edit', compact('timesheet', 'tasks'));
    }

    public function update(UpdateTimesheetRequest $request, Timesheet $timesheet, TimesheetIntegrationResolver $resolver, CostCalculator $calculator, CompanySettings $settings): RedirectResponse
    {
        $this->authorizePermission('timesheet edit');
        abort_unless($timesheet->created_by == (function_exists('creatorId') ? creatorId() : $timesheet->created_by), 403);

        $data = $request->validated();

        $companyId = function_exists('company') && company() ? company()->id : $timesheet->company_id;

        $timesheet->user_id = (int) ($data['user_id'] ?? $timesheet->user_id);
        $timesheet->project_id = $data['project_id'] ?? null;
        $timesheet->task_id = $data['task_id'] ?? null;
        $timesheet->work_order_id = $data['work_order_id'] ?? null;
        $timesheet->date = $data['date'];
        $timesheet->hours = (int) ($data['hours'] ?? 0);
        $timesheet->minutes = (int) ($data['minutes'] ?? 0);
        $timesheet->type = $data['type'] ?? 'regular';
        $timesheet->notes = $data['notes'] ?? null;

        // Recompute rate + cost
        $hr = $resolver->hrm();
        $rate = $hr->getRatePerHour($timesheet->user_id, $companyId);
        if ($rate !== null) {
            $timesheet->fsm_rate_per_hour = $rate;
        }

        if ($companyId && $settings->bool($companyId, 'costing_enabled', config('timesheet.features.costing_enabled', true)) === false) {
            $timesheet->fsm_cost_total = null;
        } else {
            $timesheet->fsm_cost_total = $calculator->computeCostTotal($timesheet);
        }

        $timesheet->save();

        event(new UpdateTimesheet($timesheet));

        return redirect()->route('timesheet.index')->with('success', __('Timesheet::timesheet.msg.updated'));
    }

    public function destroy(Timesheet $timesheet): RedirectResponse
    {
        $this->authorizePermission('timesheet delete');
        abort_unless($timesheet->created_by == (function_exists('creatorId') ? creatorId() : $timesheet->created_by), 403);

        $timesheet->delete();

        event(new DestroyTimesheet($timesheet));

        return back()->with('success', __('Timesheet::timesheet.msg.deleted'));
    }

    public function totalhours(Request $request): JsonResponse
    {
        $this->authorizePermission('timesheet manage');

        $userId = (int) ($request->input('user_id') ?? Auth::id());
        $from = $request->input('from', Carbon::now()->startOfWeek()->toDateString());
        $to = $request->input('to', Carbon::now()->endOfWeek()->toDateString());

        $mins = Timesheet::query()->forCreator()
            ->where('user_id', $userId)
            ->whereBetween('date', [$from, $to])
            ->get()
            ->sum(function ($t) {
                return ((int) $t->hours) * 60 + ((int) $t->minutes);
            });

        return response()->json([
            'minutes' => $mins,
            'hours_decimal' => round($mins / 60.0, 2),
        ]);
    }

    public function gethours($user): JsonResponse
    {
        $this->authorizePermission('timesheet manage');

        $userId = (int) $user;
        $from = Carbon::now()->startOfWeek()->toDateString();
        $to = Carbon::now()->endOfWeek()->toDateString();

        $mins = Timesheet::query()->forCreator()
            ->where('user_id', $userId)
            ->whereBetween('date', [$from, $to])
            ->get()
            ->sum(function ($t) {
                return ((int) $t->hours) * 60 + ((int) $t->minutes);
            });

        return response()->json([
            'minutes' => $mins,
            'hours_decimal' => round($mins / 60.0, 2),
        ]);
    }

    public function gettask(Request $request, TimesheetIntegrationResolver $resolver): JsonResponse
    {
        $this->authorizePermission('timesheet manage');

        $projectId = $request->input('project_id');
        if (!$projectId) {
            return response()->json(['data' => []]);
        }

        $tasks = $resolver->tasks()->getTasksByProjectId((int) $projectId);

        return response()->json(['data' => $tasks]);
    }


    public function getWorkOrders(Request $request, TimesheetIntegrationResolver $resolver): JsonResponse
    {
        $this->authorizePermission('timesheet manage');

        $companyId = function_exists('company') && company() ? (int) company()->id : null;
        $projectId = $request->filled('project_id') ? (int) $request->input('project_id') : null;
        $search = $request->filled('q') ? (string) $request->input('q') : null;

        $rows = $resolver->workOrders()->listForSelect($companyId, $projectId, $search, 50);

        return response()->json(['data' => $rows]);
    }

    private function authorizePermission(string $permission): void
    {
        $user = Auth::user();
        if (method_exists($user, 'isAbleTo') && !$user->isAbleTo($permission)) {
            abort(403);
        }
    }
}
