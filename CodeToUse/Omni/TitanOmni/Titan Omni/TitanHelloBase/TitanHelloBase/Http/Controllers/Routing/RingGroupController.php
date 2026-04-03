<?php

namespace Modules\TitanHello\Http\Controllers\Routing;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\TitanHello\Models\RingGroup;
use Modules\TitanHello\Models\RingGroupMember;

class RingGroupController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;

        $groups = RingGroup::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->paginate(25);

        return view('titanhello::routing/ringgroups/index', compact('groups'));
    }

    public function create()
    {
        return view('titanhello::routing/ringgroups/create');
    }

    public function store(Request $request)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'strategy' => 'required|in:simultaneous,round_robin,sequential',
            'timeout_seconds' => 'required|integer|min:5|max:60',
            'enabled' => 'nullable|boolean',
        ]);

        $data['company_id'] = $companyId;
        $data['enabled'] = (bool)($data['enabled'] ?? true);

        $group = RingGroup::create($data);

        return redirect()->route('titanhello.routing.ringgroups.edit', $group->id)->with('success', 'Ring group created.');
    }

    public function edit(Request $request, int $id)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;
        $group = RingGroup::query()->where('company_id', $companyId)->findOrFail($id);
        $members = $group->members()->orderBy('priority')->get();

        return view('titanhello::routing/ringgroups/edit', compact('group', 'members'));
    }

    public function update(Request $request, int $id)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;
        $group = RingGroup::query()->where('company_id', $companyId)->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'strategy' => 'required|in:simultaneous,round_robin,sequential',
            'timeout_seconds' => 'required|integer|min:5|max:60',
            'enabled' => 'nullable|boolean',
        ]);

        $data['enabled'] = (bool)($data['enabled'] ?? false);

        $group->update($data);

        return redirect()->route('titanhello.routing.ringgroups.edit', $group->id)->with('success', 'Ring group updated.');
    }

    public function addMember(Request $request, int $id)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;
        $group = RingGroup::query()->where('company_id', $companyId)->findOrFail($id);

        $data = $request->validate([
            'label' => 'nullable|string|max:120',
            'phone_number' => 'required|string|max:32',
            'priority' => 'required|integer|min:1|max:999',
            'enabled' => 'nullable|boolean',
        ]);

        $data['ring_group_id'] = $group->id;
        $data['enabled'] = (bool)($data['enabled'] ?? true);

        RingGroupMember::create($data);

        return redirect()->route('titanhello.routing.ringgroups.edit', $group->id)->with('success', 'Member added.');
    }

    public function deleteMember(Request $request, int $id, int $memberId)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;
        $group = RingGroup::query()->where('company_id', $companyId)->findOrFail($id);

        $member = RingGroupMember::query()->where('ring_group_id', $group->id)->findOrFail($memberId);
        $member->delete();

        return redirect()->route('titanhello.routing.ringgroups.edit', $group->id)->with('success', 'Member removed.');
    }

    public function destroy(Request $request, int $id)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;
        $group = RingGroup::query()->where('company_id', $companyId)->findOrFail($id);
        $group->members()->delete();
        $group->delete();

        return redirect()->route('titanhello.routing.ringgroups.index')->with('success', 'Ring group deleted.');
    }
}
