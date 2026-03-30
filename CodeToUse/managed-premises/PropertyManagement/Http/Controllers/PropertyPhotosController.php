<?php

namespace Modules\PropertyManagement\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Modules\PropertyManagement\Entities\Property;
use Modules\PropertyManagement\Entities\PropertyPhoto;
use Modules\PropertyManagement\Http\Requests\StorePropertyPhotoRequest;

class PropertyPhotosController extends AccountBaseController
{
    public function index(Property $property)
    {
        $this->authorize('view', $property);

        $photos = PropertyPhoto::where('company_id', company()->id)
            ->where('property_id', $property->id)
            ->latest()
            ->get();

        return view('propertymanagement::properties.photos', compact('property', 'photos'));
    }

    public function store(StorePropertyPhotoRequest $request, Property $property)
    {
        $this->authorize('update', $property);

        $path = Files::uploadLocalOrS3($request->file('photo'), PropertyPhoto::FILE_PATH);

        PropertyPhoto::create([
            'company_id' => company()->id,
            'property_id' => $property->id,
            'path' => $path,
            'caption' => $request->caption,
        ]);

        return Reply::success(__('messages.recordSaved'));
    }

    public function destroy(Property $property, PropertyPhoto $photo)
    {
        $this->authorize('update', $property);

        if ((int)$photo->company_id !== (int)company()->id) {
            abort(403);
        }

        Files::deleteFile($photo->path, PropertyPhoto::FILE_PATH);
        $photo->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }
}
