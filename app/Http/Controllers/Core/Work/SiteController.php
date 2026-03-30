<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Work;

use App\Http\Controllers\Core\CoreController;
use App\Models\Work\Site;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteController extends CoreController
{
    public function index(Request $request): View
    {
        $query = Site::query();

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(static function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('reference', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%');
            });
        }

        $sites = $query->latest()->paginate(10)->withQueryString();

        return view('default.panel.work.sites.index', [
            'sites'  => $sites,
            'search' => $search ?? '',
            'status' => $status ?? '',
        ]);
    }

    public function create(): View
    {
        return view('default.panel.work.sites.form', [
            'site' => new Site(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $site = Site::query()->create($data);

        return to_route('dashboard.work.sites.show', $site)->with([
            'type'    => 'success',
            'message' => __('Site created.'),
        ]);
    }

    public function show(Site $site): View
    {
        return view('default.panel.work.sites.show', [
            'site' => $site->load('jobs'),
        ]);
    }

    public function edit(Site $site): View
    {
        return view('default.panel.work.sites.form', [
            'site' => $site,
        ]);
    }

    public function update(Request $request, Site $site): RedirectResponse
    {
        $data = $this->validated($request);
        $site->update($data);

        return to_route('dashboard.work.sites.show', $site)->with([
            'type'    => 'success',
            'message' => __('Site updated.'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'reference'  => ['nullable', 'string', 'max:100'],
            'address'    => ['nullable', 'string', 'max:255'],
            'status'     => ['nullable', 'string', 'max:50'],
            'start_date' => ['nullable', 'date'],
            'deadline'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'notes'      => ['nullable', 'string'],
        ]);
    }
}
