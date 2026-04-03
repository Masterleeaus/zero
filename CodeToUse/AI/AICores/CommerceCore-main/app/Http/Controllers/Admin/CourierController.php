<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use Illuminate\Http\Request;

class CourierController extends Controller
{
    /**
     * Display a listing of couriers.
     */
    public function index()
    {
        $couriers = Courier::withCount('shipments')->latest()->get();
        return view('admin.couriers.index', compact('couriers'));
    }

    /**
     * Store a newly created courier.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255'
        ]);

        Courier::create($validated);
        return back()->with('success', 'Courier added successfully.');
    }

    /**
     * Display courier details and wallet transations.
     */
    public function show(Courier $courier)
    {
        $courier->load(['payments' => fn($q) => $q->latest()]);
        return view('admin.couriers.show', compact('courier'));
    }
}
