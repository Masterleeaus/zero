<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use App\Support\WorkcoreDemoData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QuoteTemplateController extends CoreController
{
    public function index(): View
    {
        return view('default.panel.user.money.quote-templates.index', [
            'templates' => WorkcoreDemoData::quoteTemplates(),
        ]);
    }

    public function create(): View
    {
        return view('default.panel.user.money.quote-templates.form', [
            'template' => null,
            'items'    => WorkcoreDemoData::lineItemsSeed(),
        ]);
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
        return view('default.panel.user.money.quote-templates.show', [
            'template' => WorkcoreDemoData::quoteTemplates()->firstWhere('name', $template)
                ?? WorkcoreDemoData::quoteTemplates()->first(),
        ]);
    }

    public function edit(string $template): View
    {
        return view('default.panel.user.money.quote-templates.form', [
            'template' => WorkcoreDemoData::quoteTemplates()->firstWhere('name', $template),
            'items'    => WorkcoreDemoData::lineItemsSeed(),
        ]);
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
