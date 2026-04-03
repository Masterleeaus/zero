<?php

declare(strict_types=1);

namespace App\Extensions\TitanTrust\System\Http\Controllers;

use App\Extensions\TitanTrust\System\Models\WorkEvidenceRule;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RulesController extends Controller
{
    public function index()
    {
        $userId = (int) auth()->id();
        $companyId = (int) (auth()->user()->company_id ?? $userId);

        $rules = WorkEvidenceRule::query()
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->latest('id')
            ->get();

        return view('titantrust::rules.index', compact('rules'));
    }

    public function create()
    {
        return view('titantrust::rules.create');
    }

    public function store(Request $request)
    {
        $userId = (int) auth()->id();
        $companyId = (int) (auth()->user()->company_id ?? $userId);

        $data = $request->validate([
            'template_id' => ['nullable', 'integer'],
            'job_type' => ['nullable', 'string', 'max:100'],
            'site_type' => ['nullable', 'string', 'max:100'],
            'req_before' => ['nullable', 'integer', 'min:0', 'max:100'],
            'req_after' => ['nullable', 'integer', 'min:0', 'max:100'],
            'req_incident' => ['nullable', 'integer', 'min:0', 'max:100'],
            'req_signoff' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        WorkEvidenceRule::query()->create([
            'company_id' => $companyId,
            'user_id' => $userId,
            'template_id' => $data['template_id'] ?? null,
            'job_type' => $data['job_type'] ?? null,
            'site_type' => $data['site_type'] ?? null,
            'required' => [
                'before' => (int) ($data['req_before'] ?? 0),
                'after' => (int) ($data['req_after'] ?? 0),
                'incident' => (int) ($data['req_incident'] ?? 0),
                'signoff' => (int) ($data['req_signoff'] ?? 0),
            ],
        ]);

        return redirect()->route('dashboard.user.titan-trust.rules.index')->with('success', 'Rule saved.');
    }

    public function edit(int $id)
    {
        $userId = (int) auth()->id();
        $companyId = (int) (auth()->user()->company_id ?? $userId);

        $rule = WorkEvidenceRule::query()
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();

        return view('titantrust::rules.edit', compact('rule'));
    }

    public function update(Request $request, int $id)
    {
        $userId = (int) auth()->id();
        $companyId = (int) (auth()->user()->company_id ?? $userId);

        $rule = WorkEvidenceRule::query()
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();

        $data = $request->validate([
            'template_id' => ['nullable', 'integer'],
            'job_type' => ['nullable', 'string', 'max:100'],
            'site_type' => ['nullable', 'string', 'max:100'],
            'req_before' => ['nullable', 'integer', 'min:0', 'max:100'],
            'req_after' => ['nullable', 'integer', 'min:0', 'max:100'],
            'req_incident' => ['nullable', 'integer', 'min:0', 'max:100'],
            'req_signoff' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $rule->update([
            'template_id' => $data['template_id'] ?? null,
            'job_type' => $data['job_type'] ?? null,
            'site_type' => $data['site_type'] ?? null,
            'required' => [
                'before' => (int) ($data['req_before'] ?? 0),
                'after' => (int) ($data['req_after'] ?? 0),
                'incident' => (int) ($data['req_incident'] ?? 0),
                'signoff' => (int) ($data['req_signoff'] ?? 0),
            ],
        ]);

        return redirect()->route('dashboard.user.titan-trust.rules.index')->with('success', 'Rule updated.');
    }

    public function destroy(int $id)
    {
        $userId = (int) auth()->id();
        $companyId = (int) (auth()->user()->company_id ?? $userId);

        WorkEvidenceRule::query()
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->where('id', $id)
            ->delete();

        return redirect()->route('dashboard.user.titan-trust.rules.index')->with('success', 'Rule deleted.');
    }
}
