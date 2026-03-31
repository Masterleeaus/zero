<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaxController extends CoreController
{
    public function index(): View
    {
        return $this->placeholder(
            __('Tax rates'),
            __('Company tax rates sourced from WorkCore.')
        );
    }

    public function create(): View
    {
        return $this->placeholder(
            __('Create tax rate'),
            __('Define a tax rate for quotes and invoices.')
        );
    }

    public function store(Request $request): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Tax rate created.'),
        ]);
    }

    public function show(string $tax): View
    {
        return $this->placeholder(
            __('Tax rate detail'),
            __('Details for tax rate :tax.', ['tax' => $tax])
        );
    }

    public function edit(string $tax): View
    {
        return $this->placeholder(
            __('Edit tax rate'),
            __('Update tax rate :tax.', ['tax' => $tax])
        );
    }

    public function update(Request $request, string $tax): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Tax rate :tax updated.', ['tax' => $tax]),
        ]);
    }

    public function destroy(string $tax): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Tax rate :tax removed.', ['tax' => $tax]),
        ]);
    }

    public function setDefault(string $tax): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Tax rate :tax set as default.', ['tax' => $tax]),
        ]);
    }
}
