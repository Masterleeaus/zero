<?php

namespace Modules\PropertyManagement\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Modules\PropertyManagement\Entities\Property;
use Modules\PropertyManagement\Entities\PropertyJob;

class PropertyJobsController extends AccountBaseController
{
    public function index($propertyId)
    {
        $viewPermission = user()->permission('propertymanagement.jobs.view');
        abort_403(!in_array($viewPermission, ['all', 'owned', 'both']));

        $this->property = Property::with('jobs')->findOrFail($propertyId);
        return view('propertymanagement::properties.jobs', $this->data);
    }

    public function store(Request $request, $propertyId)
    {
        $editPermission = user()->permission('propertymanagement.jobs.create');
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
        $editPermission = user()->permission('propertymanagement.jobs.create');
        abort_403(!in_array($editPermission, ['all']));

        $job = PropertyJob::where('property_id', $propertyId)->findOrFail($jobId);
        $job->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }
}
