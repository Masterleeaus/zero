<?php

namespace Modules\ManagedPremises\Http\Controllers;


use Modules\ManagedPremises\Http\Controllers\Concerns\EnsuresManagedPremisesPermissions;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Modules\ManagedPremises\Http\Requests\StorePropertySettingsRequest;
use Modules\ManagedPremises\Support\Settings;

class PropertySettingsController extends AccountBaseController
{
    
    use EnsuresManagedPremisesPermissions;
public function index()
    {
        $this->ensureCanViewManagedPremises();
        abort_unless(user()->can('managedpremises.settings'), 403);

        $settings = Settings::get(company()->id);
        return view('managedpremises::settings.index', compact('settings'));
    }

    public function store(StorePropertySettingsRequest $request)
    {
        $this->ensureCanViewManagedPremises();
        abort_unless(user()->can('managedpremises.settings'), 403);

        Settings::set(company()->id, $request->validated());
        return Reply::success(__('messages.updateSuccess'));
    }
}
