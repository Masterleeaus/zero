<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Route;

use App\Events\Route\RouteCreated;
use App\Events\Route\RouteUpdated;
use App\Http\Controllers\Core\CoreController;
use App\Models\Route\DispatchRoute;
use App\Models\Route\TechnicianAvailability;
use App\Models\Team\Team;
use App\Models\User;
use App\Models\Work\ServiceArea;
use App\Models\Work\Territory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DispatchRouteController extends CoreController
{
    private array $statuses = ['active', 'paused', 'archived'];

    public function index(Request $request): View
    {
        $query = DispatchRoute::query()->with(['assignedUser', 'team']);

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($teamId = $request->integer('team_id')) {
            $query->where('team_id', $teamId);
        }

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $routes = $query->latest()->paginate(25)->withQueryString();

        return view('default.panel.user.work.routes.index', [
            'routes'   => $routes,
            'teams'    => $this->teams(),
            'statuses' => $this->statuses,
            'filters'  => [
                'status'  => $status ?? '',
                'team_id' => $teamId ?? '',
                'search'  => $search ?? '',
            ],
        ]);
    }

    public function create(): View
    {
        return view('default.panel.user.work.routes.form', [
            'route'      => null,
            'users'      => $this->users(),
            'teams'      => $this->teams(),
            'territories' => $this->territories(),
            'statuses'   => $this->statuses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRoute($request);
        $data['company_id'] = auth()->user()->company_id;
        $data['created_by'] = auth()->id();

        $route = DispatchRoute::create($data);

        event(new RouteCreated($route));

        return redirect()->route('dashboard.work.routes.show', $route)
            ->with('success', __('route.created'));
    }

    public function show(DispatchRoute $route): View
    {
        $route->load(['assignedUser', 'team', 'routeStops' => function ($q) {
            $q->orderBy('route_date', 'desc')->limit(30);
        }]);

        return view('default.panel.user.work.routes.show', [
            'route' => $route,
        ]);
    }

    public function edit(DispatchRoute $route): View
    {
        return view('default.panel.user.work.routes.form', [
            'route'       => $route,
            'users'       => $this->users(),
            'teams'       => $this->teams(),
            'territories' => $this->territories(),
            'statuses'    => $this->statuses,
        ]);
    }

    public function update(Request $request, DispatchRoute $route): RedirectResponse
    {
        $data = $this->validateRoute($request, $route->id);
        $route->update($data);

        event(new RouteUpdated($route));

        return redirect()->route('dashboard.work.routes.show', $route)
            ->with('success', __('route.updated'));
    }

    public function destroy(DispatchRoute $route): RedirectResponse
    {
        $route->update(['status' => 'archived']);

        return redirect()->route('dashboard.work.routes.index')
            ->with('success', __('route.archived'));
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function validateRoute(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'               => 'required|string|max:255',
            'assigned_user_id'   => 'nullable|integer|exists:users,id',
            'team_id'            => 'nullable|integer|exists:teams,id',
            'active_days_mask'   => 'nullable|integer|min:0|max:127',
            'max_stops_per_day'  => 'nullable|integer|min:0',
            'territory_id'       => 'nullable|integer',
            'service_area_id'    => 'nullable|integer',
            'status'             => 'nullable|in:active,paused,archived',
            'notes'              => 'nullable|string|max:5000',
        ]);
    }

    private function users(): \Illuminate\Database\Eloquent\Collection
    {
        return User::query()
            ->where('company_id', auth()->user()->company_id)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function teams(): \Illuminate\Database\Eloquent\Collection
    {
        return Team::query()
            ->where('company_id', auth()->user()->company_id)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function territories(): \Illuminate\Database\Eloquent\Collection
    {
        if (!class_exists(Territory::class)) {
            return collect();
        }

        return Territory::query()
            ->where('company_id', auth()->user()->company_id)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
