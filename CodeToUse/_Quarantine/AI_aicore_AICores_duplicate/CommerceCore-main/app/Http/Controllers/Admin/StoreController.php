<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Store;
use App\Services\StoreService;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(private StoreService $storeService) {}

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            $stores = Store::with('owner', 'plan')->latest()->paginate(15);
        } else {
            $stores = $user->ownedStores()->with('plan')->latest()->paginate(15);
        }

        return view('admin.stores.index', compact('stores'));
    }

    public function create()
    {
        $plans = Plan::where('is_active', true)->get();
        return view('admin.stores.create', compact('plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:stores',
            'description' => 'nullable|string',
            'plan_id' => 'nullable|exists:plans,id',
            'logo' => 'nullable|image|max:2048',
        ]);

        if (isset($validated['logo'])) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $validated['owner_id'] = $request->user()->id;
        $store = $this->storeService->create($validated);

        // Set user as store_owner
        if ($request->user()->role === 'customer') {
            $request->user()->update(['role' => 'store_owner']);
        }

        return redirect()->route('admin.dashboard')
            ->with('success', 'Store created successfully!');
    }

    public function edit(Store $store)
    {
        $plans = Plan::where('is_active', true)->get();
        return view('admin.stores.edit', compact('store', 'plans'));
    }

    public function update(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:stores,slug,' . $store->id,
            'description' => 'nullable|string',
            'plan_id' => 'nullable|exists:plans,id',
            'logo' => 'nullable|image|max:2048',
            'status' => 'nullable|in:active,inactive,suspended',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $this->storeService->update($store, $validated);

        return redirect()->route('admin.stores.index')
            ->with('success', 'Store updated successfully!');
    }

    public function settings(Store $store)
    {
        $store->load('settings.theme');
        return view('admin.stores.settings', compact('store'));
    }

    public function updateSettings(Request $request, Store $store)
    {
        $validatedSettings = $request->validate([
            'primary_color' => 'nullable|string',
            'secondary_color' => 'nullable|string',
            'font' => 'nullable|string',
            'theme_id' => 'nullable|exists:themes,id',
            'favicon' => 'nullable|image|max:1024',
            'ecom_logo' => 'nullable|image|max:2048',
            'facebook_pixel_id' => 'nullable|string|max:50',
        ]);

        if ($request->hasFile('favicon')) {
            $validatedSettings['favicon'] = $request->file('favicon')->store('favicons', 'public');
        }
        if ($request->hasFile('ecom_logo')) {
            $validatedSettings['ecom_logo'] = $request->file('ecom_logo')->store('logos', 'public');
        }

        $validatedStore = $request->validate([
            'name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validatedStore['logo'] = $request->file('logo')->store('logos', 'public');
        }

        if (isset($validatedSettings['facebook_pixel_id'])) {
            $validatedStore['facebook_pixel_id'] = $validatedSettings['facebook_pixel_id'];
            unset($validatedSettings['facebook_pixel_id']);
        }

        if(!empty($validatedStore)) {
            $store->update($validatedStore);
        }

        $this->storeService->updateSettings($store, $validatedSettings);

        return redirect()->back()->with('success', 'Settings updated!');
    }

    public function destroy(Store $store)
    {
        $store->delete();
        return redirect()->route('admin.stores.index')
            ->with('success', 'Store deleted successfully!');
    }
}
