<?php
namespace Modules\PropertyManagement\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Modules\PropertyManagement\Entities\Property;
use Modules\PropertyManagement\Entities\PropertyTag;
use Modules\PropertyManagement\Http\Requests\StorePropertyTagRequest;

class PropertyTagsController extends AccountBaseController
{
    public function index(Property $property)
    {
        $this->authorize('view', $property);

        $tags = PropertyTag::where('company_id', company()->id)
            ->where('property_id', $property->id)
            ->latest()
            ->get();

        $storeUrl = route('propertymanagement.properties.tags.store', $property->id);
        return view('propertymanagement::properties.tags', compact('property','tags','storeUrl'));
    }

    public function store(StorePropertyTagRequest $request, Property $property)
    {
        $this->authorize('update', $property);

        PropertyTag::create([
            'company_id' => company()->id,
            'property_id' => $property->id,
            'tag' => $request->tag,
        ]);

        return Reply::success(__('messages.recordSaved'));
    }

    public function destroy(Property $property, PropertyTag $tag)
    {
        $this->authorize('update', $property);
        abort_unless((int)$tag->company_id === (int)company()->id, 403);

        $tag->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }
}
