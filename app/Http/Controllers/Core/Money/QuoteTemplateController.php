<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QuoteTemplateController extends CoreController
{
    public function index(): View
    {
        return $this->placeholder(
            __('Quote templates'),
            __('Reusable quote templates from WorkCore.')
        );
    }

    public function create(): View
    {
        return $this->placeholder(
            __('Create quote template'),
            __('Set up a reusable quote layout.')
        );
    }

    public function store(Request $request): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Quote template created.'),
        ]);
    }

    public function show(string $template): View
    {
        return $this->placeholder(
            __('Quote template detail'),
            __('Template :template detail view.', ['template' => $template])
        );
    }

    public function edit(string $template): View
    {
        return $this->placeholder(
            __('Edit quote template'),
            __('Update template :template.', ['template' => $template])
        );
    }

    public function update(Request $request, string $template): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Template :template updated.', ['template' => $template]),
        ]);
    }

    public function destroy(string $template): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Template :template removed.', ['template' => $template]),
        ]);
    }

    public function applyToQuote(Request $request, string $template): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Template :template applied to quote.', ['template' => $template]),
        ]);
    }
}

