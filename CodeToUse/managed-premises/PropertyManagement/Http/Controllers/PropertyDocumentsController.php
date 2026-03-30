<?php
namespace Modules\PropertyManagement\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\PropertyManagement\Entities\Property;
use Modules\PropertyManagement\Entities\PropertyDocument;

class PropertyDocumentsController extends Controller
{
    public function index(Property $property)
    {
        $this->authorize('view', $property);
        $docs = PropertyDocument::company()->where('property_id', $property->id)->latest()->paginate(25);
        return view('propertymanagement::documents.index', compact('property','docs'));
    }

    public function create(Property $property)
    {
        $this->authorize('update', $property);
        return view('propertymanagement::documents.create', compact('property'));
    }

    public function store(Request $request, Property $property)
    {
        $this->authorize('update', $property);

        $data = $request->validate([
            'name' => ['required','string','max:190'],
            'doc_type' => ['nullable','string','max:120'],
            'file' => ['required','file','max:20480'],
            'notes' => ['nullable','string'],
        ]);

        $path = $request->file('file')->store('pm/property-docs/'.company()->id.'/'.$property->id, ['disk' => config('filesystems.default')]);

        PropertyDocument::create([
            'company_id' => company()->id,
            'property_id' => $property->id,
            'name' => $data['name'],
            'doc_type' => $data['doc_type'] ?? null,
            'stored_path' => $path,
            'mime' => $request->file('file')->getMimeType(),
            'size_bytes' => $request->file('file')->getSize(),
            'uploaded_by' => user()->id ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('propertymanagement.properties.show', $property)->with('success', __('propertymanagement::app.saved'));
    }

    public function download(Property $property, PropertyDocument $doc)
    {
        $this->authorize('view', $property);
        abort_unless($doc->property_id === $property->id, 404);

        return Storage::download($doc->stored_path, $doc->name);
    }

    public function destroy(Property $property, PropertyDocument $doc)
    {
        $this->authorize('update', $property);
        abort_unless($doc->property_id === $property->id, 404);

        Storage::delete($doc->stored_path);
        $doc->delete();

        return back()->with('success', __('propertymanagement::app.deleted'));
    }
}
