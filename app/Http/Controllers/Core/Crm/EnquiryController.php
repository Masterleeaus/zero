<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Crm;

use App\Http\Controllers\Core\CoreController;
use App\Models\Crm\Customer;
use App\Models\Crm\Enquiry;
use App\Models\Money\Quote;
use App\Services\Crm\CrmServiceJobService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnquiryController extends CoreController
{
    public function index(Request $request): View
    {
        $query = Enquiry::query()->with(['customer', 'assignedUser']);

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(static function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('source', 'like', '%' . $search . '%');
            });
        }

        $enquiries = $query->latest()->paginate(25)->withQueryString();

        return view('default.panel.user.crm.enquiries.index', [
            'enquiries' => $enquiries,
            'search'    => $search,
        ]);
    }

    public function create(): View
    {
        return view('default.panel.user.crm.enquiries.form', [
            'enquiry'   => new Enquiry(),
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $enquiry = Enquiry::query()->create($data);

        return to_route('dashboard.crm.enquiries.show', $enquiry)->with([
            'type'    => 'success',
            'message' => __('Enquiry created.'),
        ]);
    }

    public function show(Enquiry $enquiry): View
    {
        return view('default.panel.user.crm.enquiries.show', [
            'enquiry' => $enquiry->load('customer'),
        ]);
    }

    public function update(Request $request, Enquiry $enquiry): RedirectResponse
    {
        $data = $this->validated($request);

        $enquiry->update($data);

        return back()->with([
            'type'    => 'success',
            'message' => __('Enquiry updated.'),
        ]);
    }

    public function convertToQuote(Enquiry $enquiry): RedirectResponse
    {
        $this->authorize('update', $enquiry);

        $quote = DB::transaction(function () use ($enquiry) {
            $q = Quote::create([
                'company_id'  => $enquiry->company_id,
                'created_by'  => auth()->id(),
                'customer_id' => $enquiry->customer_id,
                'enquiry_id'  => $enquiry->id,
                'title'       => $enquiry->name,
                'status'      => 'draft',
                'currency'    => auth()->user()->company->currency ?? 'AUD',
            ]);

            $enquiry->update(['status' => 'quoted', 'quote_id' => $q->id]);

            return $q;
        });

        return redirect()->route('dashboard.money.quotes.edit', $quote)
            ->with('status', __('Quote created from enquiry.'));
    }

    /**
     * Create a ServiceJob from this enquiry (CRM lead → service conversion).
     *
     * Module 6 (fieldservice_crm) — enquiry → service-job conversion action.
     */
    public function convertToServiceJob(Enquiry $enquiry, CrmServiceJobService $service): RedirectResponse
    {
        $this->authorize('update', $enquiry);

        $job = $service->createJobFromEnquiry($enquiry);

        return redirect()->route('dashboard.work.service-jobs.show', $job)
            ->with([
                'type'    => 'success',
                'message' => __('Service job created from enquiry.'),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['nullable', 'email', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:50'],
            'source'      => ['nullable', 'string', 'max:100'],
            'status'      => ['nullable', 'string', 'max:50'],
            'notes'       => ['nullable', 'string'],
        ]);
    }
}
