<?php

namespace Modules\PropertyManagement\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Modules\PropertyManagement\Http\Requests\StorePropertySettingsRequest;
use Modules\PropertyManagement\Support\Settings;

class PropertySettingsController extends AccountBaseController
{
    public function index()
    {
        abort_unless(user()->can('propertymanagement.settings'), 403);

        $settings = Settings::get(company()->id);
        return view('propertymanagement::settings.index', compact('settings'));
    }

    public function store(StorePropertySettingsRequest $request)
    {
        abort_unless(user()->can('propertymanagement.settings'), 403);

        Settings::set(company()->id, $request->validated());
        return Reply::success(__('messages.updateSuccess'));
    }
}
