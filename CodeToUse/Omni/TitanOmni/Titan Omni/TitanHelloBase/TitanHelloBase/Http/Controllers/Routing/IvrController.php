<?php

namespace Modules\TitanHello\Http\Controllers\Routing;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\TitanHello\Models\IvrMenu;
use Modules\TitanHello\Models\IvrOption;

class IvrController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;

        $menus = IvrMenu::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->paginate(25);

        return view('titanhello::routing/ivr/index', compact('menus'));
    }

    public function create()
    {
        return view('titanhello::routing/ivr/create');
    }

    public function store(Request $request)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'greeting_text' => 'nullable|string',
            'repeat_count' => 'required|integer|min:1|max:5',
            'timeout_seconds' => 'required|integer|min:3|max:20',
            'enabled' => 'nullable|boolean',
        ]);

        $data['company_id'] = $companyId;
        $data['enabled'] = (bool)($data['enabled'] ?? true);

        $menu = IvrMenu::create($data);

        return redirect()->route('titanhello.routing.ivr.edit', $menu->id)->with('success', 'IVR menu created.');
    }

    public function edit(Request $request, int $id)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;
        $menu = IvrMenu::query()->where('company_id', $companyId)->findOrFail($id);
        $options = $menu->options()->orderBy('dtmf')->get();

        return view('titanhello::routing/ivr/edit', compact('menu', 'options'));
    }

    public function update(Request $request, int $id)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;
        $menu = IvrMenu::query()->where('company_id', $companyId)->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'greeting_text' => 'nullable|string',
            'repeat_count' => 'required|integer|min:1|max:5',
            'timeout_seconds' => 'required|integer|min:3|max:20',
            'enabled' => 'nullable|boolean',
        ]);

        $data['enabled'] = (bool)($data['enabled'] ?? false);
        $menu->update($data);

        return redirect()->route('titanhello.routing.ivr.edit', $menu->id)->with('success', 'IVR menu updated.');
    }

    public function addOption(Request $request, int $id)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;
        $menu = IvrMenu::query()->where('company_id', $companyId)->findOrFail($id);

        $data = $request->validate([
            'dtmf' => 'required|string|max:4',
            'label' => 'nullable|string|max:120',
            'action_type' => 'required|in:ring_group,voicemail,hangup,ivr',
            'action_target_id' => 'nullable|integer',
            'enabled' => 'nullable|boolean',
        ]);

        $data['ivr_menu_id'] = $menu->id;
        $data['enabled'] = (bool)($data['enabled'] ?? true);

        IvrOption::create($data);

        return redirect()->route('titanhello.routing.ivr.edit', $menu->id)->with('success', 'Option added.');
    }

    public function deleteOption(Request $request, int $id, int $optId)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;
        $menu = IvrMenu::query()->where('company_id', $companyId)->findOrFail($id);

        $opt = IvrOption::query()->where('ivr_menu_id', $menu->id)->findOrFail($optId);
        $opt->delete();

        return redirect()->route('titanhello.routing.ivr.edit', $menu->id)->with('success', 'Option removed.');
    }

    public function destroy(Request $request, int $id)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;
        $menu = IvrMenu::query()->where('company_id', $companyId)->findOrFail($id);
        $menu->options()->delete();
        $menu->delete();

        return redirect()->route('titanhello.routing.ivr.index')->with('success', 'IVR menu deleted.');
    }
}
