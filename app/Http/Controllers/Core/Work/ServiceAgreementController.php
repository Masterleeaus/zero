<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Work;

use App\Http\Controllers\Core\CoreController;
use App\Models\Crm\Customer;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use App\Services\Work\AgreementSchedulerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceAgreementController extends CoreController
{
    public function index(Request $request): View
    {
        $agreements = ServiceAgreement::query()
            ->where('company_id', $request->user()?->company_id)
            ->with(['customer', 'site'])
            ->latest()
            ->paginate(15);

        return view('default.panel.user.work.agreements.index', compact('agreements'));
    }

    public function create(Request $request): View
    {
        $customers = Customer::query()->where('company_id', $request->user()?->company_id)->orderBy('name')->get(['id', 'name']);
        $sites = Site::query()->where('company_id', $request->user()?->company_id)->orderBy('name')->get(['id', 'name']);

        return view('default.panel.user.work.agreements.create', compact('customers', 'sites'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'site_id'     => ['nullable', 'exists:sites,id'],
            'frequency'   => ['required', 'string', 'max:50'],
            'next_run_at' => ['nullable', 'date'],
            'status'      => ['required', 'string', 'max:50'],
        ]);

        ServiceAgreement::query()->create([
            'company_id'  => $request->user()?->company_id,
            'title'       => $validated['title'],
            'customer_id' => $validated['customer_id'] ?? null,
            'site_id'     => $validated['site_id'] ?? null,
            'frequency'   => $validated['frequency'],
            'next_run_at' => $validated['next_run_at'] ?? null,
            'status'      => $validated['status'],
        ]);

        return redirect()->route('dashboard.work.agreements.index')
            ->with('message', __('Agreement created'));
    }

    public function show(Request $request, ServiceAgreement $agreement): View
    {
        abort_if($agreement->company_id !== $request->user()?->company_id, 403);

        $jobs = ServiceJob::query()
            ->where('company_id', $agreement->company_id)
            ->where('agreement_id', $agreement->id)
            ->orderBy('scheduled_at')
            ->get();

        return view('default.panel.user.work.agreements.show', [
            'agreement' => $agreement->load(['customer', 'site']),
            'jobs'      => $jobs,
        ]);
    }

    public function run(Request $request, ServiceAgreement $agreement, AgreementSchedulerService $scheduler): RedirectResponse
    {
        abort_if($agreement->company_id !== $request->user()?->company_id, 403);

        $scheduler->runForCompany($agreement->company_id);

        return back()->with('message', __('Agreement scheduled jobs generated'));
    }
}
