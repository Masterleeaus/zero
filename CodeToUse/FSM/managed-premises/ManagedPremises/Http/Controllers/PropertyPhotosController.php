<?php

namespace Modules\ManagedPremises\Http\Controllers;


use Modules\ManagedPremises\Http\Controllers\Concerns\EnsuresManagedPremisesPermissions;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Modules\ManagedPremises\Entities\Property;
use Modules\ManagedPremises\Entities\PropertyPhoto;
use Modules\ManagedPremises\Http\Requests\StorePropertyPhotoRequest;

class PropertyPhotosController extends AccountBaseController
{
    
    use EnsuresManagedPremisesPermissions;
/**
     * Sidebar-friendly global list of photos (optionally filtered by premise).
     * Includes quick upload.
     */
    public function globalIndex(Request $request)
    {
        $this->ensureCanViewManagedPremises();
        $properties = Property::where('company_id', company()->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $propertyId = (int) ($request->get('property_id') ?? 0);

        $query = PropertyPhoto::where('company_id', company()->id)
            ->with('property')
            ->latest();

        if ($propertyId > 0) {
            $query->where('property_id', $propertyId);
        }

        $photos = $query->get();

        return view('managedpremises::global.photos', [
            'properties' => $properties,
            'propertyId' => $propertyId,
            'photos' => $photos,
            'storeUrl' => route('managedpremises.photos.store'),
        ]);
    }

    /**
     * Upload a photo from the global list page.
     */
    public function globalStore(Request $request)
    {
        $this->ensureCanViewManagedPremises();
        $data = $request->validate([
            'property_id' => ['required', 'integer'],
            'property_job_id' => ['nullable', 'integer'],
            'photo' => ['required', 'file', 'max:20480'],
            'caption' => ['nullable', 'string', 'max:190'],
        ]);

        $property = Property::where('company_id', company()->id)->findOrFail($data['property_id']);
        $this->authorize('update', $property);

        // Optional job link must belong to the same property
        if (!empty($data['property_job_id'])) {
            $exists = \Modules\ManagedPremises\Entities\PropertyJob::where('company_id', company()->id)
                ->where('property_id', $property->id)
                ->where('id', $data['property_job_id'])
                ->exists();
            if (!$exists) {
                abort(422, 'Invalid job for this premise.');
            }
        }

        $path = Files::uploadLocalOrS3($request->file('photo'), PropertyPhoto::FILE_PATH);

        PropertyPhoto::create([
            'company_id' => company()->id,
            'property_id' => $property->id,
            'property_job_id' => $data['property_job_id'] ?? null,
            'path' => $path,
            'caption' => $data['caption'] ?? null,
        ]);

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Delete a photo from the global list.
     */
    public function globalDestroy(PropertyPhoto $photo)
    {
        $this->ensureCanViewManagedPremises();
        if ((int)$photo->company_id !== (int)company()->id) {
            abort(403);
        }

        // Ensure we can update the premise it belongs to
        $property = Property::where('company_id', company()->id)->find($photo->property_id);
        if ($property) {
            $this->authorize('update', $property);
        }

        Files::deleteFile($photo->path, PropertyPhoto::FILE_PATH);
        $photo->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function index(Property $property)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('view', $property);

        $photos = PropertyPhoto::where('company_id', company()->id)
            ->where('property_id', $property->id)
            ->latest()
            ->get();

        return view('managedpremises::properties.photos', compact('property', 'photos'));
    }

    public function store(StorePropertyPhotoRequest $request, Property $property)
    {
        $this->ensureCanViewManagedPremises();
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
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);

        if ((int)$photo->company_id !== (int)company()->id) {
            abort(403);
        }

        Files::deleteFile($photo->path, PropertyPhoto::FILE_PATH);
        $photo->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }
}
