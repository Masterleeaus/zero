<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Crm;

use App\Http\Controllers\Core\CoreController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerContactController extends CoreController
{
    public function index(string $customer): View
    {
        return $this->placeholder(
            __('Customer contacts'),
            __('WorkCore contact records for this customer will appear here.')
        );
    }

    public function create(string $customer): View
    {
        return $this->placeholder(
            __('New customer contact'),
            __('Capture contact details for the selected customer.')
        );
    }

    public function store(Request $request, string $customer): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Contact saved for customer :customer.', ['customer' => $customer]),
        ]);
    }

    public function show(string $customer, string $contact): View
    {
        return $this->placeholder(
            __('Contact detail'),
            __('View details for contact :contact on customer :customer.', [
                'contact'  => $contact,
                'customer' => $customer,
            ])
        );
    }

    public function edit(string $customer, string $contact): View
    {
        return $this->placeholder(
            __('Edit contact'),
            __('Update contact :contact for customer :customer.', [
                'contact'  => $contact,
                'customer' => $customer,
            ])
        );
    }

    public function update(Request $request, string $customer, string $contact): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Contact :contact updated.', ['contact' => $contact]),
        ]);
    }

    public function destroy(string $customer, string $contact): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Contact :contact removed.', ['contact' => $contact]),
        ]);
    }
}
