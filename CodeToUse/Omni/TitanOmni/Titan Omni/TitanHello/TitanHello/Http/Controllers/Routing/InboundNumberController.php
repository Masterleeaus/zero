<?php

namespace Modules\TitanHello\Http\Controllers\Routing;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\TitanHello\Models\InboundNumber;

class InboundNumberController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;

        $numbers = InboundNumber::query()
            ->where('company_id', $companyId)
            ->orderBy('phone_number')
            ->paginate(25);

        return view('titanhello::routing/numbers/index', compact('numbers'));
    }

    public function create()
    {
        return view('titanhello::routing/numbers/create');
    }

    public function store(Request $request)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;

        $data = $request->validate([
            'phone_number' => 'required|string|max:32',
            'label' => 'nullable|string|max:100',
            'mode' => 'required|in:ring_group,ivr',
            'target_id' => 'nullable|integer',
            'enabled' => 'nullable|boolean',
            'business_hours_only' => 'nullable|boolean',
            'after_hours_mode' => 'nullable|in:ring_group,ivr,voicemail,hangup',
            'after_hours_target_id' => 'nullable|integer',
        ]);

        $data['company_id'] = $companyId;
        $data['enabled'] = (bool)($data['enabled'] ?? true);
        $data['business_hours_only'] = (bool)($data['business_hours_only'] ?? false);

        InboundNumber::create($data);

        return redirect()->route('titanhello.routing.numbers.index')->with('success', 'Inbound number saved.');
    }

    public function edit(Request $request, int $id)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;
        $number = InboundNumber::query()->where('company_id', $companyId)->findOrFail($id);

        return view('titanhello::routing/numbers/edit', compact('number'));
    }

    public function update(Request $request, int $id)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;
        $number = InboundNumber::query()->where('company_id', $companyId)->findOrFail($id);

        $data = $request->validate([
            'phone_number' => 'required|string|max:32',
            'label' => 'nullable|string|max:100',
            'mode' => 'required|in:ring_group,ivr',
            'target_id' => 'nullable|integer',
            'enabled' => 'nullable|boolean',
            'business_hours_only' => 'nullable|boolean',
            'after_hours_mode' => 'nullable|in:ring_group,ivr,voicemail,hangup',
            'after_hours_target_id' => 'nullable|integer',
        ]);

        $data['enabled'] = (bool)($data['enabled'] ?? false);
        $data['business_hours_only'] = (bool)($data['business_hours_only'] ?? false);

        $number->update($data);

        return redirect()->route('titanhello.routing.numbers.index')->with('success', 'Inbound number updated.');
    }

    public function destroy(Request $request, int $id)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;
        $number = InboundNumber::query()->where('company_id', $companyId)->findOrFail($id);
        $number->delete();

        return redirect()->route('titanhello.routing.numbers.index')->with('success', 'Inbound number deleted.');
    }
}
