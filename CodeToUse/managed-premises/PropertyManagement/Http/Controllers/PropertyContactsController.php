<?php

namespace Modules\PropertyManagement\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Modules\PropertyManagement\Entities\Property;
use Modules\PropertyManagement\Entities\PropertyContact;

class PropertyContactsController extends AccountBaseController
{
    public function index($propertyId)
    {
        $viewPermission = user()->permission('propertymanagement.contacts.view');
        abort_403(!in_array($viewPermission, ['all', 'owned', 'both']));

        $this->property = Property::with('contacts')->findOrFail($propertyId);
        return view('propertymanagement::properties.contacts', $this->data);
    }

    public function store(Request $request, $propertyId)
    {
        $editPermission = user()->permission('propertymanagement.contacts.create');
        abort_403(!in_array($editPermission, ['all']));

        $validated = $request->validate([
            'role' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $property = Property::findOrFail($propertyId);
        $property->contacts()->create($validated);

        return Reply::success(__('messages.recordSaved'));
    }

    public function destroy($propertyId, $contactId)
    {
        $editPermission = user()->permission('propertymanagement.contacts.create');
        abort_403(!in_array($editPermission, ['all']));

        $contact = PropertyContact::where('property_id', $propertyId)->findOrFail($contactId);
        $contact->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }
}
