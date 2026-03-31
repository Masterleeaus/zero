<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Crm;

use App\Http\Controllers\Core\CoreController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DealController extends CoreController
{
    public function index(): View
    {
        return $this->placeholder(
            __('Deals'),
            __('Pipeline views and deal list will appear here.')
        );
    }

    public function create(): View
    {
        return $this->placeholder(
            __('Create deal'),
            __('Start a new deal in the pipeline.')
        );
    }

    public function store(Request $request): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Deal created.'),
        ]);
    }

    public function show(string $deal): View
    {
        return $this->placeholder(
            __('Deal detail'),
            __('Deal :deal details and history.', ['deal' => $deal])
        );
    }

    public function edit(string $deal): View
    {
        return $this->placeholder(
            __('Edit deal'),
            __('Update information for deal :deal.', ['deal' => $deal])
        );
    }

    public function update(Request $request, string $deal): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Deal :deal updated.', ['deal' => $deal]),
        ]);
    }

    public function destroy(string $deal): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Deal :deal archived.', ['deal' => $deal]),
        ]);
    }

    public function kanban(): View
    {
        return $this->placeholder(
            __('Deal board'),
            __('Kanban pipeline view sourced from WorkCore.')
        );
    }

    public function updateStatus(Request $request, string $deal): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Deal :deal status updated.', ['deal' => $deal]),
        ]);
    }
}

