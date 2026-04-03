<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketingCampaign;
use App\Models\User;
use App\Services\NotificationService;
use App\Traits\ResolvesStore;
use Illuminate\Http\Request;

class MarketingController extends Controller
{
    use ResolvesStore;

    public function __construct(private NotificationService $notificationService) {}

    public function updateSettings(Request $request)
    {
        $store = $this->getActiveStore($request);

        $validated = $request->validate([
            'facebook_pixel_id' => 'nullable|string|max:50',
        ]);

        $store->update($validated);

        return back()->with('success', 'Marketing tracking settings updated successfully.');
    }

    public function index(Request $request)
    {
        $store = $this->getActiveStore($request);

        $campaigns = MarketingCampaign::where('store_id', $store->id)
            ->latest()
            ->paginate(20);

        return view('admin.marketing.index', compact('campaigns'));
    }

    public function store(Request $request)
    {
        $store = $this->getActiveStore($request);

        $validated = $request->validate([
            'name'        => 'required|string',
            'type'        => 'required|in:sms,email,whatsapp,push',
            'message'     => 'required|string',
            'target_rank' => 'nullable|string',
        ]);

        // Only target customers of this store
        $recipientsQuery = User::where('role', 'customer')
            ->where('store_id', $store->id);

        if ($validated['target_rank']) {
            $recipientsQuery->where('customer_rank', $validated['target_rank']);
        }

        $recipients = $recipientsQuery->get()->map(function ($user) use ($validated) {
            return [
                'user_id' => $user->id,
                'address' => $validated['type'] === 'email'
                    ? $user->email
                    : ($user->phone ?? 'unknown'),
            ];
        });

        $sentCount = $this->notificationService->bulkSend(
            $recipients,
            $validated['type'],
            $validated['message']
        );

        MarketingCampaign::create(array_merge($validated, [
            'store_id'         => $store->id,
            'recipients_count' => $sentCount,
            'status'           => 'completed',
        ]));

        return back()->with('success', "Campaign '{$validated['name']}' sent to {$sentCount} customers.");
    }
}
