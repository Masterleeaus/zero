<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use App\Models\Crm\Customer;
use App\Models\Money\Invoice;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class InvoiceController extends CoreController
{
    public function index(Request $request): View
    {
        $query = Invoice::query()->with(['customer', 'quote']);

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(static function ($builder) use ($search) {
                $builder->where('number', 'like', '%' . $search . '%')
                    ->orWhere('title', 'like', '%' . $search . '%');
            });
        }

        $invoices = $query->latest('issue_date')->latest()->paginate(10)->withQueryString();

        return view('default.panel.user.money.invoices.index', [
            'invoices' => $invoices,
            'filters'  => [
                'status' => $status ?? '',
                'search' => $search ?? '',
            ],
        ]);
    }

    public function show(Invoice $invoice): View
    {
        return view('default.panel.user.money.invoices.show', [
            'invoice' => $invoice->load(['customer', 'quote']),
        ]);
    }
}
