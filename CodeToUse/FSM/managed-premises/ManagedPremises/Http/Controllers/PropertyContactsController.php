<?php

namespace Modules\ManagedPremises\Http\Controllers;


use Modules\ManagedPremises\Http\Controllers\Concerns\EnsuresManagedPremisesPermissions;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Modules\ManagedPremises\Entities\Property;
use Modules\ManagedPremises\Entities\PropertyContact;

class PropertyContactsController extends AccountBaseController
{
    
    use EnsuresManagedPremisesPermissions;
public function index($propertyId)
    {
        $this->ensureCanViewManagedPremises();
        $viewPermission = user()->permission('managedpremises.contacts.view');
        abort_403(!in_array($viewPermission, ['all', 'owned', 'both']));

        $this->property = Property::with('contacts')->findOrFail($propertyId);
        return view('managedpremises::properties.contacts', $this->data);
    }

    public function store(Request $request, $propertyId)
    {
        $this->ensureCanViewManagedPremises();
        $editPermission = user()->permission('managedpremises.contacts.create');
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
        $this->ensureCanViewManagedPremises();
        $editPermission = user()->permission('managedpremises.contacts.create');
        abort_403(!in_array($editPermission, ['all']));

        $contact = PropertyContact::where('property_id', $propertyId)->findOrFail($contactId);
        $contact->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }
}
