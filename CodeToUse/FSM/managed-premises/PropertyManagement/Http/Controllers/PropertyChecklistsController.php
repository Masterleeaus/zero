<?php

namespace Modules\PropertyManagement\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Modules\PropertyManagement\Entities\Property;
use Modules\PropertyManagement\Entities\PropertyChecklist;
use Modules\PropertyManagement\Http\Requests\StorePropertyChecklistRequest;

class PropertyChecklistsController extends AccountBaseController
{
    public function index(Property $property)
    {
        $this->authorize('view', $property);

        $checklists = PropertyChecklist::where('company_id', company()->id)
            ->where('property_id', $property->id)
            ->latest()
            ->get();

        return view('propertymanagement::properties.checklists', compact('property', 'checklists'));
    }

    public function store(StorePropertyChecklistRequest $request, Property $property)
    {
        $this->authorize('update', $property);

        PropertyChecklist::create([
            'company_id' => company()->id,
            'property_id' => $property->id,
            'type' => $request->type,
            'title' => $request->title,
            'items_json' => json_encode($request->items, JSON_UNESCAPED_UNICODE),
        ]);

        return Reply::success(__('messages.recordSaved'));
    }

    public function destroy(Property $property, PropertyChecklist $checklist)
    {
        $this->authorize('update', $property);

        if ((int)$checklist->company_id !== (int)company()->id) {
            abort(403);
        }

        $checklist->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }
}
