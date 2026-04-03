<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Store;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $store = $request->get('admin_store') ?? Store::find(session('admin_store_id'));
        
        if (!$store) {
            return redirect()->route('admin.dashboard')->with('error', 'Please select a store first.');
        }

        $branches = $store->branches;
        return view('admin.branches.index', compact('branches', 'store'));
    }

    public function create()
    {
        return view('admin.branches.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'is_primary' => 'nullable|boolean'
        ]);

        $store = Store::find(session('admin_store_id'));

        if ($validated['is_primary'] ?? false) {
            $store->branches()->update(['is_primary' => false]);
        }

        $store->branches()->create($validated);

        return redirect()->route('admin.branches.index')->with('success', 'Branch added successfully!');
    }

    public function edit(Branch $branch)
    {
        return view('admin.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'is_primary' => 'nullable|boolean'
        ]);

        if ($validated['is_primary'] ?? false) {
            $branch->store->branches()->update(['is_primary' => false]);
        }

        $branch->update($validated);

        return redirect()->route('admin.branches.index')->with('success', 'Branch updated successfully!');
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();
        return redirect()->route('admin.branches.index')->with('success', 'Branch removed.');
    }
}
