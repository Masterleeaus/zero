<?php

namespace Modules\PropertyManagement\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Modules\PropertyManagement\Entities\Property;
use Modules\PropertyManagement\Http\Requests\StorePropertyRequest;
use Modules\PropertyManagement\Http\Requests\UpdatePropertyRequest;

class PropertiesController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'propertymanagement::app.menu.properties';
        $this->pageIcon = 'ti-home';
    }

    public function index()
    {
        $viewPermission = user()->permission('propertymanagement.view');
        abort_403(!in_array($viewPermission, ['all', 'owned', 'both']));

        $this->properties = Property::orderByDesc('id')->paginate(25);
        return view('propertymanagement::properties.index', $this->data);
    }

    public function create()
    {
        $addPermission = user()->permission('propertymanagement.create');
        abort_403(!in_array($addPermission, ['all']));

        $this->property = new Property();
        $this->view = 'propertymanagement::properties.create';

        if (request()->ajax()) {
            $html = view('propertymanagement::properties.ajax.form', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => __('propertymanagement::app.menu.properties')]);
        }

        return view('propertymanagement::properties.create', $this->data);
    }

    public function store(StorePropertyRequest $request)
    {
        $property = Property::create(array_merge(
            $request->validated(),
            ['created_by' => user()->id ?? null]
        ));

        $redirectUrl = route('propertymanagement.properties.show', $property->id);
        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function show($id)
    {
        $viewPermission = user()->permission('propertymanagement.view');
        abort_403(!in_array($viewPermission, ['all', 'owned', 'both']));

        $this->property = Property::with(['units', 'contacts', 'jobs'])->findOrFail($id);

        if (request()->ajax()) {
            $html = view('propertymanagement::properties.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->property->name ?? __('propertymanagement::app.menu.properties')]);
        }

        return view('propertymanagement::properties.show', $this->data);
    }

    public function edit($id)
    {
        $editPermission = user()->permission('propertymanagement.update');
        abort_403(!in_array($editPermission, ['all']));

        $this->property = Property::findOrFail($id);

        if (request()->ajax()) {
            $html = view('propertymanagement::properties.ajax.form', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => __('app.edit')]);
        }

        return view('propertymanagement::properties.edit', $this->data);
    }

    public function update(UpdatePropertyRequest $request, $id)
    {
        $property = Property::findOrFail($id);
        $property->fill($request->validated());
        $property->save();

        $redirectUrl = route('propertymanagement.properties.show', $property->id);
        return Reply::successWithData(__('messages.recordUpdated'), ['redirectUrl' => $redirectUrl]);
    }

    public function destroy($id)
    {
        $deletePermission = user()->permission('propertymanagement.delete');
        abort_403(!in_array($deletePermission, ['all']));

        Property::destroy($id);

        $redirectUrl = route('propertymanagement.properties.index');
        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => $redirectUrl]);
    }
}
