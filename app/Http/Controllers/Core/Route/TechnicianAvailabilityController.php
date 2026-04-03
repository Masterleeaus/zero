<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Route;

use App\Events\Route\TechnicianAvailabilityCreated;
use App\Events\Route\TechnicianAvailabilityUpdated;
use App\Http\Controllers\Core\CoreController;
use App\Models\Route\TechnicianAvailability;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TechnicianAvailabilityController extends CoreController
{
    public function index(Request $request): View
    {
        $query = TechnicianAvailability::query()->with(['user', 'team']);

        if ($userId = $request->integer('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($teamId = $request->integer('team_id')) {
            $query->where('team_id', $teamId);
        }

        if ($isActive = $request->string('is_active')->toString()) {
            $query->where('is_active', $isActive === '1');
        }

        $availabilities = $query->latest()->paginate(25)->withQueryString();

        return view('default.panel.user.work.routes.availability.index', [
            'availabilities' => $availabilities,
            'users'          => $this->users(),
            'teams'          => $this->teams(),
            'filters'        => [
                'user_id'   => $userId ?? '',
                'team_id'   => $teamId ?? '',
                'is_active' => $isActive ?? '',
            ],
        ]);
    }

    public function create(): View
    {
        return view('default.panel.user.work.routes.availability.form', [
            'availability' => null,
            'users'        => $this->users(),
            'teams'        => $this->teams(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateAvailability($request);
        $data['company_id'] = auth()->user()->company_id;

        $availability = TechnicianAvailability::create($data);

        event(new TechnicianAvailabilityCreated($availability));

        return redirect()->route('dashboard.work.routes.availability.index')
            ->with('success', __('route.availability_created'));
    }

    public function edit(TechnicianAvailability $availability): View
    {
        return view('default.panel.user.work.routes.availability.form', [
            'availability' => $availability,
            'users'        => $this->users(),
            'teams'        => $this->teams(),
        ]);
    }

    public function update(Request $request, TechnicianAvailability $availability): RedirectResponse
    {
        $data = $this->validateAvailability($request, $availability->id);
        $availability->update($data);

        event(new TechnicianAvailabilityUpdated($availability));

        return redirect()->route('dashboard.work.routes.availability.index')
            ->with('success', __('route.availability_updated'));
    }

    public function destroy(TechnicianAvailability $availability): RedirectResponse
    {
        $availability->update(['is_active' => false]);

        return redirect()->route('dashboard.work.routes.availability.index')
            ->with('success', __('route.availability_deactivated'));
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function validateAvailability(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'user_id'          => 'required|integer|exists:users,id',
            'team_id'          => 'nullable|integer|exists:teams,id',
            'name'             => 'nullable|string|max:255',
            'active_days_mask' => 'nullable|integer|min:0|max:127',
            'work_start_time'  => 'nullable|date_format:H:i',
            'work_end_time'    => 'nullable|date_format:H:i',
            'max_work_hours'   => 'nullable|numeric|min:0|max:24',
            'overtime_allowed' => 'nullable|boolean',
            'is_active'        => 'nullable|boolean',
            'valid_from'       => 'nullable|date',
            'valid_until'      => 'nullable|date|after_or_equal:valid_from',
            'notes'            => 'nullable|string|max:5000',
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
}
