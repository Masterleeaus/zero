<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Work;

use App\Http\Controllers\Core\CoreController;
use App\Models\Work\JobStage;
use App\Models\Work\JobType;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use App\Models\Work\Territory;
use App\Models\Crm\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceJobController extends CoreController
{
    private array $statuses   = ['scheduled', 'in_progress', 'completed', 'cancelled'];
    private array $priorities = ['normal', 'low', 'high', 'urgent'];

    public function index(Request $request): View
    {
        $query = ServiceJob::query()->with(['site', 'customer', 'assignedUser', 'quote', 'agreement', 'stage', 'jobType']);

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($stageId = $request->integer('stage_id')) {
            $query->where('stage_id', $stageId);
        }

        if ($priority = $request->string('priority')->toString()) {
            $query->where('priority', $priority);
        }

        if ($territoryId = $request->integer('territory_id')) {
            $query->where('territory_id', $territoryId);
        }

        if ($siteId = $request->integer('site_id')) {
            $query->where('site_id', $siteId);
        }

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(static function ($builder) use ($search) {
                $builder->where('title', 'like', '%' . $search . '%')
                    ->orWhere('notes', 'like', '%' . $search . '%');
            });
        }

        $jobs = $query->latest()->paginate(25)->withQueryString();

        return view('default.panel.user.work.jobs.index', [
            'jobs'        => $jobs,
            'sites'       => $this->sites(),
            'stages'      => $this->stages(),
            'priorities'  => $this->priorities,
            'territories' => $this->territories(),
            'filters'     => [
                'status'       => $status ?? '',
                'stage_id'     => $stageId ?? '',
                'priority'     => $priority ?? '',
                'territory_id' => $territoryId ?? '',
                'site_id'      => $siteId ?? '',
                'search'       => $search ?? '',
            ],
        ]);
    }

    public function create(Request $request): View
    {
        return view('default.panel.user.work.jobs.form', [
            'job'         => new ServiceJob(),
            'sites'       => $this->sites(),
            'customers'   => $this->customers(),
            'siteId'      => $request->integer('site_id') ?: null,
            'statuses'    => $this->statuses,
            'priorities'  => $this->priorities,
            'stages'      => $this->stages(),
            'jobTypes'    => $this->jobTypes(),
            'territories' => $this->territories(),
            'assignees'   => $this->assignees(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $job = ServiceJob::query()->create($data);

        return to_route('dashboard.work.service-jobs.show', $job)->with([
            'type'    => 'success',
            'message' => __('Service job created.'),
        ]);
    }

    public function show(ServiceJob $job): View
    {
        return view('default.panel.user.work.jobs.show', [
            'job' => $job->load(['site', 'customer', 'checklists', 'stage', 'jobType', 'territory']),
        ]);
    }

    public function edit(ServiceJob $job): View
    {
        return view('default.panel.user.work.jobs.form', [
            'job'         => $job,
            'sites'       => $this->sites(),
            'customers'   => $this->customers(),
            'siteId'      => $job->site_id,
            'statuses'    => $this->statuses,
            'priorities'  => $this->priorities,
            'stages'      => $this->stages(),
            'jobTypes'    => $this->jobTypes(),
            'territories' => $this->territories(),
            'assignees'   => $this->assignees(),
        ]);
    }

    public function update(Request $request, ServiceJob $job): RedirectResponse
    {
        $data = $this->validated($request);

        $job->update($data);

        return to_route('dashboard.work.service-jobs.show', $job)->with([
            'type'    => 'success',
            'message' => __('Service job updated.'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'site_id'              => [
                'required',
                'integer',
                Rule::exists('sites', 'id')->where(static function ($query) {
                    return $query->when(auth()->check(), function ($q) {
                        $q->where('company_id', auth()->user()->company_id);
                    });
                }),
            ],
            'customer_id'          => [
                'nullable',
                'integer',
                Rule::exists('customers', 'id')->where(static function ($query) {
                    return $query->when(auth()->check(), function ($q) {
                        $q->where('company_id', auth()->user()->company_id);
                    });
                }),
            ],
            'title'                => ['required', 'string', 'max:255'],
            'status'               => ['nullable', Rule::in($this->statuses)],
            'stage_id'             => [
                'nullable',
                'integer',
                Rule::exists('job_stages', 'id')->where(static function ($query) {
                    return $query->when(auth()->check(), function ($q) {
                        $q->where('company_id', auth()->user()->company_id);
                    });
                }),
            ],
            'job_type_id'          => [
                'nullable',
                'integer',
                Rule::exists('job_types', 'id')->where(static function ($query) {
                    return $query->when(auth()->check(), function ($q) {
                        $q->where('company_id', auth()->user()->company_id);
                    });
                }),
            ],
            'priority'             => ['nullable', Rule::in($this->priorities)],
            'territory_id'         => ['nullable', 'integer', Rule::exists('territories', 'id')],
            'scheduled_at'         => ['nullable', 'date'],
            'scheduled_date_start' => ['nullable', 'date'],
            'scheduled_date_end'   => ['nullable', 'date', 'after_or_equal:scheduled_date_start'],
            'scheduled_duration'   => ['nullable', 'numeric', 'min:0'],
            'date_start'           => ['nullable', 'date'],
            'date_end'             => ['nullable', 'date', 'after_or_equal:date_start'],
            'notes'                => ['nullable', 'string'],
            'todo'                 => ['nullable', 'string'],
            'resolution'           => ['nullable', 'string'],
            'signed_by'            => ['nullable', 'string', 'max:255'],
            'signed_on'            => ['nullable', 'date'],
            'require_signature'    => ['nullable', 'boolean'],
            'assigned_user_id'     => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) use ($request) {
                    return $query->where('company_id', $request->user()->company_id);
                }),
            ],
        ]);
    }

    private function sites()
    {
        return Site::query()->orderBy('name')->get(['id', 'name']);
    }

    private function customers()
    {
        return Customer::query()
            ->when(auth()->check(), fn ($q) => $q->where('company_id', auth()->user()->company_id))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function assignees()
    {
        return \App\Models\User::query()
            ->when(auth()->check(), fn ($q) => $q->where('company_id', auth()->user()->company_id))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function stages()
    {
        return JobStage::query()
            ->when(auth()->check(), fn ($q) => $q->where('company_id', auth()->user()->company_id))
            ->where('stage_type', 'order')
            ->orderBy('sequence')
            ->get(['id', 'name']);
    }

    private function jobTypes()
    {
        return JobType::query()
            ->when(auth()->check(), fn ($q) => $q->where('company_id', auth()->user()->company_id))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function territories()
    {
        return Territory::query()->orderBy('name')->get(['id', 'name']);
    }
}
