<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Work;

use App\Http\Controllers\Core\CoreController;
use App\Models\Vehicle\Vehicle;
use App\Models\Vehicle\VehicleLocationSnapshot;
use App\Models\Vehicle\VehicleStock;
use App\Models\Work\ServiceJob;
use App\Services\FSM\VehicleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * VehicleController — CRUD + lifecycle actions for crew vehicles.
 *
 * Exposes the vehicle domain to the dashboard work panel.
 * Routes are defined in routes/core/work.routes.php under
 * dashboard.work.vehicles.*.
 */
class VehicleController extends CoreController
{
    public function __construct(private readonly VehicleService $vehicleService) {}

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $vehicles = Vehicle::where('company_id', $request->user()->company_id)
            ->orderBy('name')
            ->paginate(20);

        return view('default.panel.user.work.vehicles.index', compact('vehicles'));
    }

    // ── Create / Store ────────────────────────────────────────────────────────

    public function create(): View
    {
        return view('default.panel.user.work.vehicles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:120'],
            'registration'      => ['nullable', 'string', 'max:30'],
            'vehicle_type'      => ['nullable', 'string', 'in:' . implode(',', Vehicle::TYPES)],
            'team_id'           => ['nullable', 'integer', 'exists:teams,id'],
            'assigned_driver_id' => ['nullable', 'integer', 'exists:users,id'],
            'make'              => ['nullable', 'string', 'max:60'],
            'model'             => ['nullable', 'string', 'max:60'],
            'year'              => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'capacity_kg'       => ['nullable', 'integer', 'min:0'],
            'capability_tags'   => ['nullable', 'array'],
            'capability_tags.*' => ['string'],
            'status'            => ['nullable', 'string'],
            'notes'             => ['nullable', 'string'],
        ]);

        $data['company_id'] = $request->user()->company_id;

        $vehicle = Vehicle::create($data);

        return redirect()
            ->route('dashboard.work.vehicles.show', $vehicle)
            ->with('message', __('Vehicle created'));
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(Request $request, Vehicle $vehicle): View
    {
        abort_if($vehicle->company_id !== $request->user()->company_id, 403);

        $vehicle->load(['team', 'assignedDriver', 'stockItems', 'vehicleEquipment']);

        return view('default.panel.user.work.vehicles.show', compact('vehicle'));
    }

    // ── Edit / Update ─────────────────────────────────────────────────────────

    public function edit(Request $request, Vehicle $vehicle): View
    {
        abort_if($vehicle->company_id !== $request->user()->company_id, 403);

        return view('default.panel.user.work.vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        abort_if($vehicle->company_id !== $request->user()->company_id, 403);

        $data = $request->validate([
            'name'              => ['required', 'string', 'max:120'],
            'registration'      => ['nullable', 'string', 'max:30'],
            'vehicle_type'      => ['nullable', 'string', 'in:' . implode(',', Vehicle::TYPES)],
            'team_id'           => ['nullable', 'integer', 'exists:teams,id'],
            'assigned_driver_id' => ['nullable', 'integer', 'exists:users,id'],
            'make'              => ['nullable', 'string', 'max:60'],
            'model'             => ['nullable', 'string', 'max:60'],
            'year'              => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'capacity_kg'       => ['nullable', 'integer', 'min:0'],
            'capability_tags'   => ['nullable', 'array'],
            'capability_tags.*' => ['string'],
            'status'            => ['nullable', 'string'],
            'notes'             => ['nullable', 'string'],
        ]);

        $vehicle->update($data);

        return redirect()
            ->route('dashboard.work.vehicles.show', $vehicle)
            ->with('message', __('Vehicle updated'));
    }

    // ── Job assignment ────────────────────────────────────────────────────────

    /**
     * POST dashboard/work/vehicles/{vehicle}/assign-job
     */
    public function assignJob(Request $request, Vehicle $vehicle): JsonResponse|RedirectResponse
    {
        abort_if($vehicle->company_id !== $request->user()->company_id, 403);

        $data = $request->validate([
            'service_job_id' => ['required', 'integer', 'exists:service_jobs,id'],
        ]);

        $job = ServiceJob::findOrFail($data['service_job_id']);
        abort_if($job->company_id !== $request->user()->company_id, 403);

        $assignment = $this->vehicleService->assignVehicleToJob($vehicle, $job);

        if ($request->expectsJson()) {
            return response()->json([
                'assignment_id' => $assignment->id,
                'vehicle_id'    => $vehicle->id,
                'job_id'        => $job->id,
            ]);
        }

        return redirect()
            ->route('dashboard.work.vehicles.show', $vehicle)
            ->with('message', __('Vehicle assigned to job'));
    }

    /**
     * POST dashboard/work/vehicles/{vehicle}/location-snapshot
     */
    public function recordLocation(Request $request, Vehicle $vehicle): JsonResponse
    {
        abort_if($vehicle->company_id !== $request->user()->company_id, 403);

        $data = $request->validate([
            'lat'      => ['required', 'numeric', 'between:-90,90'],
            'lng'      => ['required', 'numeric', 'between:-180,180'],
            'source'   => ['nullable', 'string', 'in:mobile,gps,manual,system'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
        ]);

        $snapshot = $this->vehicleService->recordLocationSnapshot($vehicle, $data);

        return response()->json([
            'snapshot_id' => $snapshot->id,
            'lat'         => $snapshot->lat,
            'lng'         => $snapshot->lng,
            'captured_at' => $snapshot->captured_at->toIso8601String(),
        ]);
    }

    /**
     * GET dashboard/work/vehicles/{vehicle}/compatibility/{job}
     */
    public function checkCompatibility(Request $request, Vehicle $vehicle, ServiceJob $job): JsonResponse
    {
        abort_if($vehicle->company_id !== $request->user()->company_id, 403);
        abort_if($job->company_id !== $request->user()->company_id, 403);

        $status = $this->vehicleService->checkJobCompatibility($vehicle, $job);

        return response()->json($status);
    }
}
