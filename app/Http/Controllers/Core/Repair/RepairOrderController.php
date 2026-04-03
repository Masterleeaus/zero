<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Repair;

use App\Events\Repair\RepairDiagnosisRecorded;
use App\Events\Repair\RepairOrderCompleted;
use App\Events\Repair\RepairOrderCreated;
use App\Http\Controllers\Core\CoreController;
use App\Models\Crm\Customer;
use App\Models\Equipment\Equipment;
use App\Models\Equipment\InstalledEquipment;
use App\Models\Equipment\WarrantyClaim;
use App\Models\Premises\Premises;
use App\Models\Repair\RepairDiagnosis;
use App\Models\Repair\RepairOrder;
use App\Models\Repair\RepairTemplate;
use App\Models\Team\Team;
use App\Models\User;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServiceJob;
use App\Services\Repair\RepairTemplateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * RepairOrderController
 *
 * FSM Module 9 — Repair Domain
 *
 * CRUD surface for RepairOrder lifecycle management.
 * Integrates with equipment, warranty, premises, and service job domains.
 */
class RepairOrderController extends CoreController
{
    private array $statuses   = RepairOrder::STATUSES;
    private array $priorities = [
        RepairOrder::PRIORITY_LOW,
        RepairOrder::PRIORITY_NORMAL,
        RepairOrder::PRIORITY_HIGH,
        RepairOrder::PRIORITY_URGENT,
    ];
    private array $repairTypes = [
        RepairOrder::TYPE_BREAKDOWN,
        RepairOrder::TYPE_CORRECTIVE,
        RepairOrder::TYPE_EMERGENCY,
        RepairOrder::TYPE_PREVENTIVE,
        RepairOrder::TYPE_WARRANTY,
    ];

    public function __construct(private readonly RepairTemplateService $templateService) {}

    public function index(Request $request): View
    {
        $query = RepairOrder::query()->with([
            'customer', 'equipment', 'installedEquipment', 'premises',
            'assignedUser', 'assignedTeam', 'warrantyClaim',
        ]);

        if ($request->filled('status')) {
            $query->where('repair_status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('premises_id')) {
            $query->where('premises_id', $request->premises_id);
        }

        $repairs   = $query->orderByDesc('created_at')->paginate(25)->withQueryString();
        $statuses  = $this->statuses;
        $priorities = $this->priorities;

        return view('core.repair.index', compact('repairs', 'statuses', 'priorities'));
    }

    public function create(Request $request): View
    {
        $repair = new RepairOrder();

        return view('core.repair.create', array_merge(
            compact('repair'),
            $this->formData(),
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $data['repair_number'] = $this->templateService->generateRepairNumber();
        $data['repair_status'] = RepairOrder::STATUS_DRAFT;

        $repair = RepairOrder::create($data);

        RepairOrderCreated::dispatch($repair);

        return redirect()->route('repair.orders.show', $repair)
            ->with('success', 'Repair order created.');
    }

    public function show(RepairOrder $repair): View
    {
        $repair->load([
            'customer', 'equipment', 'installedEquipment', 'siteAsset', 'premises',
            'serviceJob', 'warrantyClaim', 'agreement', 'assignedUser', 'assignedTeam',
            'template', 'diagnoses', 'tasks', 'partUsages', 'checklists', 'resolution',
        ]);

        return view('core.repair.show', compact('repair'));
    }

    public function edit(RepairOrder $repair): View
    {
        return view('core.repair.edit', array_merge(
            compact('repair'),
            $this->formData(),
        ));
    }

    public function update(Request $request, RepairOrder $repair): RedirectResponse
    {
        $repair->update($this->validated($request, $repair));

        return redirect()->route('repair.orders.show', $repair)
            ->with('success', 'Repair order updated.');
    }

    /**
     * Record a diagnosis against a repair order.
     */
    public function storeDiagnosis(Request $request, RepairOrder $repair): RedirectResponse
    {
        $data = $request->validate([
            'symptom'              => ['required', 'string'],
            'cause'                => ['nullable', 'string'],
            'recommended_action'   => ['nullable', 'string'],
            'safety_flag'          => ['boolean'],
            'requires_specialist'  => ['boolean'],
            'requires_parts'       => ['boolean'],
            'requires_quote'       => ['boolean'],
            'estimated_duration'   => ['nullable', 'integer', 'min:0'],
            'estimated_cost'       => ['nullable', 'numeric', 'min:0'],
        ]);

        $data['company_id']      = $repair->company_id;
        $data['created_by']      = auth()->id();
        $data['repair_order_id'] = $repair->id;

        $diagnosis = RepairDiagnosis::create($data);

        if ($repair->repair_status === RepairOrder::STATUS_DRAFT) {
            $repair->update(['repair_status' => RepairOrder::STATUS_DIAGNOSED]);
        }

        RepairDiagnosisRecorded::dispatch($repair, $diagnosis);

        return redirect()->route('repair.orders.show', $repair)
            ->with('success', 'Diagnosis recorded.');
    }

    /**
     * Apply a repair template to a repair order.
     */
    public function applyTemplate(Request $request, RepairOrder $repair): RedirectResponse
    {
        $request->validate([
            'repair_template_id' => ['required', 'exists:repair_templates,id'],
        ]);

        $template = RepairTemplate::findOrFail($request->repair_template_id);
        $this->templateService->applyToRepairOrder($template, $repair);

        $repair->update(['repair_template_id' => $template->id]);

        return redirect()->route('repair.orders.show', $repair)
            ->with('success', 'Template applied.');
    }

    /**
     * Mark a repair order as completed.
     */
    public function complete(RepairOrder $repair): RedirectResponse
    {
        $repair->update([
            'repair_status' => RepairOrder::STATUS_COMPLETED,
            'completed_at'  => now(),
        ]);

        RepairOrderCompleted::dispatch($repair);

        return redirect()->route('repair.orders.show', $repair)
            ->with('success', 'Repair order completed.');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function validated(Request $request, ?RepairOrder $repair = null): array
    {
        return $request->validate([
            'equipment_id'          => ['nullable', 'exists:equipment,id'],
            'installed_equipment_id' => ['nullable', 'exists:installed_equipment,id'],
            'site_asset_id'         => ['nullable', 'exists:site_assets,id'],
            'premises_id'           => ['nullable', 'exists:premises,id'],
            'service_job_id'        => ['nullable', 'exists:service_jobs,id'],
            'warranty_claim_id'     => ['nullable', 'exists:warranty_claims,id'],
            'agreement_id'          => ['nullable', 'exists:service_agreements,id'],
            'customer_id'           => ['nullable', 'exists:customers,id'],
            'assigned_team_id'      => ['nullable', 'exists:teams,id'],
            'assigned_user_id'      => ['nullable', 'exists:users,id'],
            'priority'              => ['required', Rule::in($this->priorities)],
            'severity'              => ['nullable', 'string'],
            'fault_category'        => ['nullable', 'string'],
            'repair_type'           => ['nullable', Rule::in($this->repairTypes)],
            'repair_status'         => ['nullable', Rule::in($this->statuses)],
            'requires_parts'        => ['boolean'],
            'requires_followup'     => ['boolean'],
            'requires_quote'        => ['boolean'],
            'requires_return_visit' => ['boolean'],
            'diagnosis_summary'     => ['nullable', 'string'],
            'resolution_summary'    => ['nullable', 'string'],
            'scheduled_at'          => ['nullable', 'date'],
        ]);
    }

    private function formData(): array
    {
        return [
            'statuses'      => $this->statuses,
            'priorities'    => $this->priorities,
            'repairTypes'   => $this->repairTypes,
            'customers'     => Customer::orderBy('name')->pluck('name', 'id'),
            'premises'      => Premises::orderBy('name')->pluck('name', 'id'),
            'equipment'     => Equipment::orderBy('name')->pluck('name', 'id'),
            'assignees'     => User::orderBy('name')->pluck('name', 'id'),
            'teams'         => Team::orderBy('name')->pluck('name', 'id'),
            'templates'     => RepairTemplate::active()->orderBy('name')->pluck('name', 'id'),
            'serviceJobs'   => ServiceJob::whereNotIn('status', ['completed', 'cancelled'])
                                    ->orderBy('title')->pluck('title', 'id'),
            'warrantyClaims' => WarrantyClaim::whereIn('status', ['draft', 'submitted', 'approved'])
                                    ->orderByDesc('created_at')->pluck('claim_reference', 'id'),
        ];
    }
}
