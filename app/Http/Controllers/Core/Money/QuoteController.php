<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use App\Models\Crm\Customer;
use App\Models\Money\Quote;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class QuoteController extends CoreController
{
    public function index(Request $request): View
    {
        $query = Quote::query()->with('customer');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(static function ($builder) use ($search) {
                $builder->where('number', 'like', '%' . $search . '%')
                    ->orWhere('title', 'like', '%' . $search . '%');
            });
        }

        $quotes = $query->latest('issue_date')->latest()->paginate(10)->withQueryString();

        return view('default.panel.user.money.quotes.index', [
            'quotes'  => $quotes,
            'filters' => [
                'status' => $status ?? '',
                'search' => $search ?? '',
            ],
        ]);
    }

    public function show(Quote $quote): View
    {
        return view('default.panel.user.money.quotes.show', [
            'quote' => $quote->load(['customer', 'invoices']),
        ]);
    }
}
