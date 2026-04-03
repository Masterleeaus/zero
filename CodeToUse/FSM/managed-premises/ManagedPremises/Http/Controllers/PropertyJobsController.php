<?php

namespace Modules\ManagedPremises\Http\Controllers;


use Modules\ManagedPremises\Http\Controllers\Concerns\EnsuresManagedPremisesPermissions;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Modules\ManagedPremises\Entities\Property;
use Modules\ManagedPremises\Entities\PropertyJob;

class PropertyJobsController extends AccountBaseController
{
    
    use EnsuresManagedPremisesPermissions;
public function index($propertyId)
    {
        $this->ensureCanViewManagedPremises();
        $viewPermission = user()->permission('managedpremises.jobs.view');
        abort_403(!in_array($viewPermission, ['all', 'owned', 'both']));

        $this->property = Property::with('jobs')->findOrFail($propertyId);
        return view('managedpremises::properties.jobs', $this->data);
    }

    public function store(Request $request, $propertyId)
    {
        $this->ensureCanViewManagedPremises();
        $editPermission = user()->permission('managedpremises.jobs.create');
        abort_403(!in_array($editPermission, ['all']));

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:30'],
            'scheduled_at' => ['nullable', 'date'],
            'linked_module' => ['nullable', 'string', 'max:50'],
            'linked_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],
        ]);

        $property = Property::findOrFail($propertyId);
        $property->jobs()->create(array_merge($validated, ['created_by' => user()->id ?? null]));

        return Reply::success(__('messages.recordSaved'));
    }

    public function destroy($propertyId, $jobId)
    {
        $this->ensureCanViewManagedPremises();
        $editPermission = user()->permission('managedpremises.jobs.create');
        abort_403(!in_array($editPermission, ['all']));

        $job = PropertyJob::where('property_id', $propertyId)->findOrFail($jobId);
        $job->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }
}
