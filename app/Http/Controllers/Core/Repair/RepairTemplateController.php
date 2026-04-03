<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Repair;

use App\Http\Controllers\Core\CoreController;
use App\Models\Repair\RepairTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * RepairTemplateController
 *
 * FSM Module 10 — Repair Template Engine
 *
 * CRUD surface for managing reusable repair procedure templates.
 */
class RepairTemplateController extends CoreController
{
    private array $categories = [
        'equipment_type',
        'fault_type',
        'manufacturer',
        'service_category',
        'agreement_type',
        'warranty_type',
    ];

    public function index(Request $request): View
    {
        $query = RepairTemplate::query();

        if ($request->filled('category')) {
            $query->where('template_category', $request->category);
        }

        if ($request->filled('active')) {
            $query->where('active', (bool) $request->active);
        }

        $templates  = $query->orderBy('name')->paginate(25)->withQueryString();
        $categories = $this->categories;

        return view('core.repair.templates.index', compact('templates', 'categories'));
    }

    public function create(): View
    {
        $template   = new RepairTemplate();
        $categories = $this->categories;

        return view('core.repair.templates.create', compact('template', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $template = RepairTemplate::create($data);

        return redirect()->route('repair.templates.show', $template)
            ->with('success', 'Repair template created.');
    }

    public function show(RepairTemplate $template): View
    {
        $template->load(['steps', 'parts', 'checklists']);

        return view('core.repair.templates.show', compact('template'));
    }

    public function edit(RepairTemplate $template): View
    {
        $categories = $this->categories;

        return view('core.repair.templates.edit', compact('template', 'categories'));
    }

    public function update(Request $request, RepairTemplate $template): RedirectResponse
    {
        $template->update($this->validated($request));

        return redirect()->route('repair.templates.show', $template)
            ->with('success', 'Repair template updated.');
    }

    public function destroy(RepairTemplate $template): RedirectResponse
    {
        $template->delete();

        return redirect()->route('repair.templates.index')
            ->with('success', 'Repair template deleted.');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'description'        => ['nullable', 'string'],
            'template_category'  => ['nullable', 'string'],
            'equipment_type'     => ['nullable', 'string', 'max:255'],
            'fault_type'         => ['nullable', 'string', 'max:255'],
            'manufacturer'       => ['nullable', 'string', 'max:255'],
            'service_category'   => ['nullable', 'string', 'max:255'],
            'estimated_duration' => ['nullable', 'integer', 'min:0'],
            'safety_notes'       => ['nullable', 'string'],
            'active'             => ['boolean'],
        ]);
    }
}
