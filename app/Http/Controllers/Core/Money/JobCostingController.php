<?php

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Controller;
use App\Models\Money\JobCostAllocation;
use App\Models\Work\ServiceJob;
use App\Services\TitanMoney\JobCostingService;
use App\Services\TitanMoney\ProfitabilityService;
use Illuminate\Http\Request;

class JobCostingController extends Controller
{
    public function __construct(
        protected JobCostingService $jobCosting,
        protected ProfitabilityService $profitability,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', JobCostAllocation::class);

        $allocations = JobCostAllocation::query()
            ->with(['serviceJob'])
            ->when($request->service_job_id, fn ($q, $v) => $q->forJob($v))
            ->when($request->site_id, fn ($q, $v) => $q->forSite($v))
            ->when($request->team_id, fn ($q, $v) => $q->forTeam($v))
            ->when($request->cost_type, fn ($q, $v) => $q->byCostType($v))
            ->latest()
            ->paginate(50);

        return view('money.job-costing.index', compact('allocations'));
    }

    public function show(JobCostAllocation $allocation)
    {
        $this->authorize('view', $allocation);

        $allocation->load(['serviceJob', 'journalEntry', 'createdBy']);

        return view('money.job-costing.show', compact('allocation'));
    }

    public function create()
    {
        $this->authorize('create', JobCostAllocation::class);

        $jobs        = ServiceJob::query()->latest()->get(['id', 'reference', 'description']);
        $costTypes   = JobCostAllocation::COST_TYPES;
        $sourceTypes = JobCostAllocation::SOURCE_TYPES;

        return view('money.job-costing.create', compact('jobs', 'costTypes', 'sourceTypes'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', JobCostAllocation::class);

        $validated = $request->validate([
            'service_job_id' => 'nullable|exists:service_jobs,id',
            'cost_type'      => 'required|in:' . implode(',', JobCostAllocation::COST_TYPES),
            'source_type'    => 'required|in:' . implode(',', JobCostAllocation::SOURCE_TYPES),
            'amount'         => 'required|numeric|min:0',
            'quantity'       => 'nullable|numeric|min:0',
            'unit_cost'      => 'nullable|numeric|min:0',
            'description'    => 'nullable|string|max:1000',
            'allocated_at'   => 'required|date',
            'notes'          => 'nullable|string|max:2000',
        ]);

        $allocation = $this->jobCosting->allocateManual(
            $validated,
            $request->user()->company_id,
            $request->user()->id
        );

        return redirect()->route('money.cost-allocations.show', $allocation)
            ->with('success', 'Cost allocation recorded.');
    }
}
