<?php
namespace Modules\ManagedPremises\Http\Controllers;


use Modules\ManagedPremises\Http\Controllers\Concerns\EnsuresManagedPremisesPermissions;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\ManagedPremises\Entities\Property;
use Modules\ManagedPremises\Entities\PropertyDocument;
use Modules\ManagedPremises\Entities\PropertyJob;

class PropertyDocumentsController extends AccountBaseController
{
    
    use EnsuresManagedPremisesPermissions;
/**
     * Sidebar-friendly global list of documents (optionally filtered by premise).
     */
    public function globalIndex(Request $request)
    {
        $this->ensureCanViewManagedPremises();
        $properties = Property::where('company_id', company()->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $propertyId = (int) ($request->get('property_id') ?? 0);

        $query = PropertyDocument::where('company_id', company()->id)
            ->with('property')
            ->latest();

        if ($propertyId > 0) {
            $query->where('property_id', $propertyId);
        }

        $docs = $query->paginate(25);

        return view('managedpremises::global.documents', compact('properties', 'propertyId', 'docs'));
    }

    /**
     * Upload a document from the global list page.
     */
    public function globalStore(Request $request)
    {
        $this->ensureCanViewManagedPremises();
        $data = $request->validate([
            'property_id' => ['required','integer'],
            'property_job_id' => ['nullable','integer'],
            'name' => ['required','string','max:190'],
            'doc_type' => ['nullable','string','max:120'],
            'file' => ['required','file','max:20480'],
            'notes' => ['nullable','string'],
        ]);

        $property = Property::where('company_id', company()->id)->findOrFail($data['property_id']);
        $this->authorize('update', $property);

        if (!empty($data['property_job_id'])) {
            $exists = PropertyJob::where('company_id', company()->id)
                ->where('property_id', $property->id)
                ->where('id', $data['property_job_id'])
                ->exists();
            if (!$exists) {
                abort(422, 'Invalid job for this premise.');
            }
        }

        $path = $request->file('file')->store('pm/property-docs/'.company()->id.'/'.$property->id, ['disk' => config('filesystems.default')]);

        PropertyDocument::create([
            'company_id' => company()->id,
            'property_id' => $property->id,
            'property_job_id' => $data['property_job_id'] ?? null,
            'name' => $data['name'],
            'doc_type' => $data['doc_type'] ?? null,
            'stored_path' => $path,
            'mime' => $request->file('file')->getMimeType(),
            'size_bytes' => $request->file('file')->getSize(),
            'uploaded_by' => user()->id ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return Reply::success(__('messages.recordSaved'));
    }
    public function index(Property $property)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('view', $property);
        $docs = PropertyDocument::where('company_id', company()->id)->where('property_id', $property->id)->latest()->paginate(25);
        return view('managedpremises::documents.index', compact('property','docs'));
    }

    public function create(Property $property)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);
        return view('managedpremises::documents.create', compact('property'));
    }

    public function store(Request $request, Property $property)
    {
        $this->ensureCanViewManagedPremises();
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

        return redirect()->route('managedpremises.properties.show', $property)->with('success', __('managedpremises::app.saved'));
    }

    public function download(Property $property, PropertyDocument $doc)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('view', $property);
        abort_unless($doc->property_id === $property->id, 404);

        return Storage::download($doc->stored_path, $doc->name);
    }

    public function destroy(Property $property, PropertyDocument $doc)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);
        abort_unless($doc->property_id === $property->id, 404);

        Storage::delete($doc->stored_path);
        $doc->delete();

        return back()->with('success', __('managedpremises::app.deleted'));
    }
}
