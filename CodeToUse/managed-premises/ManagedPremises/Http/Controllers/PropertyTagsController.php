<?php
namespace Modules\ManagedPremises\Http\Controllers;


use Modules\ManagedPremises\Http\Controllers\Concerns\EnsuresManagedPremisesPermissions;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Modules\ManagedPremises\Entities\Property;
use Modules\ManagedPremises\Entities\PropertyTag;
use Modules\ManagedPremises\Http\Requests\StorePropertyTagRequest;

class PropertyTagsController extends AccountBaseController
{
    
    use EnsuresManagedPremisesPermissions;
public function index(Property $property)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('view', $property);

        $tags = PropertyTag::where('company_id', company()->id)
            ->where('property_id', $property->id)
            ->latest()
            ->get();

        $storeUrl = route('managedpremises.properties.tags.store', $property->id);
        return view('managedpremises::properties.tags', compact('property','tags','storeUrl'));
    }

    public function store(StorePropertyTagRequest $request, Property $property)
    {
        $this->ensureCanViewManagedPremises();
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
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);
        abort_unless((int)$tag->company_id === (int)company()->id, 403);

        $tag->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }
}
