<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Crm;

use App\Http\Controllers\Core\CoreController;
use App\Models\Crm\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerController extends CoreController
{
    public function index(Request $request): View
    {
        $query = Customer::query();

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(static function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $customers = $query->latest()->paginate(10)->withQueryString();

        return view('default.panel.crm.customers.index', [
            'customers' => $customers,
            'search'    => $search,
        ]);
    }

    public function create(): View
    {
        return view('default.panel.crm.customers.form', [
            'customer' => new Customer(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $customer = Customer::query()->create($data);

        return to_route('dashboard.crm.customers.show', $customer)->with([
            'type'    => 'success',
            'message' => __('Customer created.'),
        ]);
    }

    public function show(Customer $customer): View
    {
        return view('default.panel.crm.customers.show', [
            'customer' => $customer,
        ]);
    }

    public function edit(Customer $customer): View
    {
        return view('default.panel.crm.customers.form', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $this->validated($request, $customer->id);

        $customer->update($data);

        return to_route('dashboard.crm.customers.show', $customer)->with([
            'type'    => 'success',
            'message' => __('Customer updated.'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?int $customerId = null): array
    {
        return $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['nullable', 'email', 'max:255'],
            'phone'  => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
            'notes'  => ['nullable', 'string'],
        ]);
    }
}
