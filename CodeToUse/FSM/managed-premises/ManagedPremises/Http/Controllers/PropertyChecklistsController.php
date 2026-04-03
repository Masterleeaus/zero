<?php

namespace Modules\ManagedPremises\Http\Controllers;


use Modules\ManagedPremises\Http\Controllers\Concerns\EnsuresManagedPremisesPermissions;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Modules\ManagedPremises\Entities\Property;
use Modules\ManagedPremises\Entities\PropertyChecklist;
use Modules\ManagedPremises\Http\Requests\StorePropertyChecklistRequest;

class PropertyChecklistsController extends AccountBaseController
{
    
    use EnsuresManagedPremisesPermissions;
public function globalIndex(Request $request)
    {
        $this->ensureCanViewManagedPremises();
        $properties = Property::where('company_id', company()->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $propertyId = (int) ($request->get('property_id') ?? 0);

        $query = PropertyChecklist::where('company_id', company()->id)
            ->with('property')
            ->latest();

        if ($propertyId > 0) {
            $query->where('property_id', $propertyId);
        }

        $checklists = $query->get();
        $storeUrl = route('managedpremises.checklists.store');

        return view('managedpremises::global.checklists', compact('properties', 'propertyId', 'checklists', 'storeUrl'));
    }

    public function globalStore(Request $request)
    {
        $this->ensureCanViewManagedPremises();
        $data = $request->validate([
            'property_id' => ['required', 'integer'],
            'type' => ['required', 'string', 'max:60'],
            'title' => ['required', 'string', 'max:191'],
            // Accept either a raw JSON string or a textarea list (one per line)
            'items' => ['nullable', 'string'],
        ]);

        $property = Property::where('company_id', company()->id)->findOrFail($data['property_id']);
        $this->authorize('update', $property);

        PropertyChecklist::create([
            'company_id' => company()->id,
            'user_id' => user()->id ?? auth()->id(),
            'property_id' => $property->id,
            'type' => $data['type'],
            'title' => $data['title'],
            'items_json' => $this->normalizeItemsToJson($data['items'] ?? null),
        ]);

        return Reply::success(__('messages.recordSaved'));
    }

    private function normalizeItemsToJson(?string $items): string
    {
        if (!$items) {
            return json_encode([], JSON_UNESCAPED_UNICODE);
        }

        // If it's already valid JSON, keep it.
        $trim = trim($items);
        if (str_starts_with($trim, '[') || str_starts_with($trim, '{')) {
            json_decode($trim, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $trim;
            }
        }

        // Otherwise treat as newline-separated checklist labels.
        $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $items) ?: [])));
        $payload = array_map(fn($l) => ['label' => $l, 'done' => false], $lines);
        return json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
    public function index(Property $property)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('view', $property);

        $checklists = PropertyChecklist::where('company_id', company()->id)
            ->where('property_id', $property->id)
            ->latest()
            ->get();

        return view('managedpremises::properties.checklists', compact('property', 'checklists'));
    }

    public function store(StorePropertyChecklistRequest $request, Property $property)
    {
        $this->ensureCanViewManagedPremises();
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
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);

        if ((int)$checklist->company_id !== (int)company()->id) {
            abort(403);
        }

        $checklist->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }
}
