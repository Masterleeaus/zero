<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Team;

use App\Http\Controllers\Core\CoreController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ZoneController extends CoreController
{
    public function index(): View
    {
        return $this->placeholder(
            __('Zones'),
            __('Operational zones and regions from WorkCore.')
        );
    }

    public function create(): View
    {
        return $this->placeholder(
            __('Create zone'),
            __('Add a geographic zone for scheduling.')
        );
    }

    public function show(string $zone): View
    {
        return $this->placeholder(
            __('Zone detail'),
            __('Details for zone :zone.', ['zone' => $zone])
        );
    }

    public function store(Request $request): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Zone created.'),
        ]);
    }

    public function edit(string $zone): View
    {
        return $this->placeholder(
            __('Edit zone'),
            __('Update zone :zone.', ['zone' => $zone])
        );
    }

    public function update(Request $request, string $zone): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Zone :zone updated.', ['zone' => $zone]),
        ]);
    }

    public function destroy(string $zone): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Zone :zone removed.', ['zone' => $zone]),
        ]);
    }
}
