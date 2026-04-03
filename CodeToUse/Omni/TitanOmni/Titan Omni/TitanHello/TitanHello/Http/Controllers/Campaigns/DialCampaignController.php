<?php

namespace Modules\TitanHello\Http\Controllers\Campaigns;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\TitanHello\Models\DialCampaign;
use Modules\TitanHello\Models\DialCampaignContact;
use Modules\TitanHello\Services\Campaigns\DialCampaignService;

class DialCampaignController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;

        $campaigns = DialCampaign::query()
            ->where('company_id', $companyId)
            ->orderByDesc('id')
            ->paginate(25);

        return view('titanhello::campaigns/index', compact('campaigns'));
    }

    public function create()
    {
        return view('titanhello::campaigns/create');
    }

    public function store(Request $request)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'from_number' => 'nullable|string|max:32',
            'max_attempts' => 'required|integer|min:1|max:10',
            'retry_minutes' => 'required|integer|min:1|max:1440',
            'enabled' => 'nullable|boolean',
        ]);

        $data['company_id'] = $companyId;
        $data['enabled'] = (bool)($data['enabled'] ?? true);

        $campaign = DialCampaign::create($data);

        return redirect()->route('titanhello.campaigns.edit', $campaign->id)->with('success', 'Campaign created.');
    }

    public function edit(Request $request, int $id)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;
        $campaign = DialCampaign::query()->where('company_id', $companyId)->findOrFail($id);

        $contacts = $campaign->contacts()->orderByDesc('id')->paginate(25);

        return view('titanhello::campaigns/edit', compact('campaign', 'contacts'));
    }

    public function update(Request $request, int $id)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;
        $campaign = DialCampaign::query()->where('company_id', $companyId)->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'from_number' => 'nullable|string|max:32',
            'max_attempts' => 'required|integer|min:1|max:10',
            'retry_minutes' => 'required|integer|min:1|max:1440',
            'enabled' => 'nullable|boolean',
            'status' => 'required|in:draft,running,paused,finished',
        ]);

        $data['enabled'] = (bool)($data['enabled'] ?? false);

        $campaign->update($data);

        return redirect()->route('titanhello.campaigns.edit', $campaign->id)->with('success', 'Campaign updated.');
    }

    public function addContact(Request $request, int $id)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;
        $campaign = DialCampaign::query()->where('company_id', $companyId)->findOrFail($id);

        $data = $request->validate([
            'name' => 'nullable|string|max:120',
            'phone_number' => 'required|string|max:32',
        ]);

        DialCampaignContact::create([
            'campaign_id' => $campaign->id,
            'name' => $data['name'] ?? null,
            'phone_number' => $data['phone_number'],
            'status' => 'pending',
        ]);

        return redirect()->route('titanhello.campaigns.edit', $campaign->id)->with('success', 'Contact added.');
    }

    public function run(Request $request, int $id, DialCampaignService $service)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;
        $campaign = DialCampaign::query()->where('company_id', $companyId)->findOrFail($id);

        $service->runOneBatch($campaign, $request->user());

        return redirect()->route('titanhello.campaigns.edit', $campaign->id)->with('success', 'Campaign batch queued.');
    }

    public function destroy(Request $request, int $id)
    {
        $companyId = $request->user()->company_id ?? $request->user()->companyId ?? 0;
        $campaign = DialCampaign::query()->where('company_id', $companyId)->findOrFail($id);
        $campaign->contacts()->delete();
        $campaign->delete();

        return redirect()->route('titanhello.campaigns.index')->with('success', 'Campaign deleted.');
    }
}
