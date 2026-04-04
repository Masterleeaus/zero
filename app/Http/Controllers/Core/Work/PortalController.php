<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Work;

use App\Http\Controllers\Controller;
use App\Models\Crm\Customer;
use App\Models\Work\FieldServiceAgreement;
use App\Models\Work\ServiceJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalController extends Controller
{
    /**
     * Portal dashboard for the authenticated customer.
     */
    public function index(Request $request)
    {
        $customer = $this->resolvePortalCustomer($request);
        if (!$customer) {
            abort(403, 'No customer profile linked to this account.');
        }

        return view('default.panel.work.portal.index', [
            'customer'         => $customer,
            'upcomingVisits'   => $customer->upcomingPortalVisits(5),
            'recentJobs'       => $customer->portalServiceHistory(5),
            'activeAgreements' => $customer->portalAgreements(),
            'openQuotes'       => $customer->portalQuotes(),
            'unpaidInvoices'   => $customer->portalInvoices()->where('status', '!=', 'paid'),
        ]);
    }

    /**
     * List all portal-visible jobs for the authenticated customer.
     */
    public function jobs(Request $request)
    {
        $customer = $this->resolvePortalCustomer($request);
        if (!$customer) {
            abort(403);
        }

        $jobs = ServiceJob::query()
            ->where('customer_id', $customer->id)
            ->whereHas('stage', fn ($q) => $q->where('portal_visible', true))
            ->with(['stage', 'premises', 'assignedUser'])
            ->latest('scheduled_date_start')
            ->paginate(20);

        return view('default.panel.work.portal.jobs', compact('customer', 'jobs'));
    }

    /**
     * View a single portal job.
     */
    public function showJob(Request $request, int $jobId)
    {
        $customer = $this->resolvePortalCustomer($request);
        if (!$customer) {
            abort(403);
        }

        $job = ServiceJob::query()
            ->where('id', $jobId)
            ->where('customer_id', $customer->id)
            ->whereHas('stage', fn ($q) => $q->where('portal_visible', true))
            ->with(['stage', 'premises', 'assignedUser', 'agreement'])
            ->firstOrFail();

        return view('default.panel.work.portal.job_show', compact('customer', 'job'));
    }

    /**
     * Portal invoices for the authenticated customer.
     */
    public function invoices(Request $request)
    {
        $customer = $this->resolvePortalCustomer($request);
        if (!$customer) {
            abort(403);
        }

        $invoices = $customer->portalInvoices()->sortByDesc('created_at');

        return view('default.panel.work.portal.invoices', compact('customer', 'invoices'));
    }

    /**
     * Portal quotes for the authenticated customer.
     */
    public function quotes(Request $request)
    {
        $customer = $this->resolvePortalCustomer($request);
        if (!$customer) {
            abort(403);
        }

        $quotes = $customer->portalQuotes()->sortByDesc('created_at');

        return view('default.panel.work.portal.quotes', compact('customer', 'quotes'));
    }

    /**
     * Portal agreements for the authenticated customer.
     */
    public function agreements(Request $request)
    {
        $customer = $this->resolvePortalCustomer($request);
        if (!$customer) {
            abort(403);
        }

        $agreements = $customer->portalAgreements()->sortByDesc('created_at');

        return view('default.panel.work.portal.agreements', compact('customer', 'agreements'));
    }

    /**
     * Portal assets/equipment for the authenticated customer.
     */
    public function assets(Request $request)
    {
        $customer = $this->resolvePortalCustomer($request);
        if (!$customer) {
            abort(403);
        }

        $assets = $customer->portalAssets();

        return view('default.panel.work.portal.assets', compact('customer', 'assets'));
    }

    /**
     * Resolve the customer linked to the authenticated user via email + company tenancy.
     */
    protected function resolvePortalCustomer(Request $request): ?Customer
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        return Customer::query()
            ->where('company_id', $user->company_id ?? null)
            ->where('email', $user->email)
            ->first();
    }

    // ── fieldservice_sale_agreement portal exposure ───────────────────────────

    /**
     * Show a single FieldServiceAgreement for the authenticated portal customer.
     */
    public function portalAgreementShow(Request $request, FieldServiceAgreement $agreement)
    {
        $customer = $this->resolvePortalCustomer($request);
        if (!$customer || (int) $agreement->customer_id !== (int) $customer->id) {
            abort(403);
        }

        $agreement->load(['premises', 'quote', 'visits', 'jobs']);

        return view('default.panel.work.portal.agreements.show', compact('customer', 'agreement'));
    }

    /**
     * Portal invoices associated with a FieldServiceAgreement.
     */
    public function portalAgreementInvoices(Request $request, FieldServiceAgreement $agreement)
    {
        $customer = $this->resolvePortalCustomer($request);
        if (!$customer || (int) $agreement->customer_id !== (int) $customer->id) {
            abort(403);
        }

        $invoices = $customer->portalInvoices()
            ->where('agreement_id', $agreement->id)
            ->sortByDesc('created_at');

        return view('default.panel.work.portal.agreements.invoices', compact('customer', 'agreement', 'invoices'));
    }

    /**
     * Portal visits associated with a FieldServiceAgreement.
     */
    public function portalAgreementVisits(Request $request, FieldServiceAgreement $agreement)
    {
        $customer = $this->resolvePortalCustomer($request);
        if (!$customer || (int) $agreement->customer_id !== (int) $customer->id) {
            abort(403);
        }

        $visits = $agreement->visits()
            ->orderBy('scheduled_date')
            ->get();

        return view('default.panel.work.portal.agreements.visits', compact('customer', 'agreement', 'visits'));
    }
}
