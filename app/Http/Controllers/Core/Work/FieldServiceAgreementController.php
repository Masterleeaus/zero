<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Work;

use App\Http\Controllers\Core\CoreController;
use App\Models\Crm\Customer;
use App\Models\Premises\Premises;
use App\Models\Work\FieldServiceAgreement;
use App\Services\Work\FieldServiceAgreementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * FieldServiceAgreementController
 *
 * Manages the contract-driven service agreement lifecycle:
 *   index → show → renew | terminate
 *
 * Routes:
 *   GET  /dashboard/work/fsm-agreements
 *   GET  /dashboard/work/fsm-agreements/{agreement}
 *   POST /dashboard/work/fsm-agreements/{agreement}/renew
 *   POST /dashboard/work/fsm-agreements/{agreement}/terminate
 */
class FieldServiceAgreementController extends CoreController
{
    public function __construct(
        private readonly FieldServiceAgreementService $service,
    ) {}

    /**
     * List all field service agreements for the authenticated company.
     */
    public function index(Request $request): View
    {
        $agreements = FieldServiceAgreement::query()
            ->where('company_id', $request->user()?->company_id)
            ->with(['customer', 'premises'])
            ->latest()
            ->paginate(15);

        return view('default.panel.work.fsm_agreements.index', compact('agreements'));
    }

    /**
     * Show a single field service agreement with execution summary.
     */
    public function show(Request $request, FieldServiceAgreement $agreement): View
    {
        $this->authorizeCompany($request, $agreement);

        $agreement->load(['customer', 'premises', 'quote', 'jobs', 'visits']);

        return view('default.panel.work.fsm_agreements.show', [
            'agreement' => $agreement,
            'summary'   => $agreement->executionSummary(),
        ]);
    }

    /**
     * Renew an agreement, creating a successor contract.
     */
    public function renew(Request $request, FieldServiceAgreement $agreement): RedirectResponse
    {
        $this->authorizeCompany($request, $agreement);

        $renewal = $this->service->renewAgreement($agreement);

        return redirect()
            ->route('dashboard.work.fsm-agreements.show', $renewal)
            ->with('message', __('Agreement renewed successfully.'));
    }

    /**
     * Terminate (cancel) an agreement.
     */
    public function terminate(Request $request, FieldServiceAgreement $agreement): RedirectResponse
    {
        $this->authorizeCompany($request, $agreement);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->service->terminateAgreement($agreement, $validated['reason'] ?? '');

        return redirect()
            ->route('dashboard.work.fsm-agreements.index')
            ->with('message', __('Agreement terminated.'));
    }

    /**
     * Assert the resource belongs to the authenticated user's company.
     */
    protected function authorizeCompany(Request $request, FieldServiceAgreement $agreement): void
    {
        if ((int) $agreement->company_id !== (int) $request->user()?->company_id) {
            abort(403);
        }
    }
}
