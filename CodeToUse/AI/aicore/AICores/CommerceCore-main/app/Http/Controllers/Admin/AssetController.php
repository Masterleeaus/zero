<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Traits\ResolvesStore;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    use ResolvesStore;

    public function index(Request $request)
    {
        $store  = $this->getActiveStore($request);
        $assets = Asset::where('store_id', $store->id)->latest()->paginate(20);
        return view('admin.assets.index', compact('assets'));
    }

    public function store(Request $request)
    {
        $store = $this->getActiveStore($request);

        $validated = $request->validate([
            'name'                    => 'required|string|max:255',
            'purchase_price'          => 'required|numeric',
            'purchase_date'           => 'required|date',
            'depreciation_percentage' => 'nullable|numeric|min:0|max:100',
            'status'                  => 'required|in:in_use,maintenance,sold,disposed',
        ]);

        Asset::create(array_merge($validated, [
            'store_id'      => $store->id,
            'current_value' => $validated['purchase_price'],
        ]));

        return back()->with('success', 'Asset added successfully.');
    }
}
