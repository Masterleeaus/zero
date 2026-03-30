<?php

namespace Modules\Units\Http\Controllers;

use App\Helper\Reply;
use App\Models\BaseModel;
use Illuminate\Http\Request;
use Modules\Units\Entities\Unit;
use Modules\Units\Entities\Floor;
use Modules\Units\Entities\Tower;
use Modules\Units\Entities\TypeUnit;
use Modules\Units\Entities\UsersUnit;
use Modules\Units\DataTables\UnitsDataTable;
use App\Http\Controllers\AccountBaseController;
use Modules\Units\Http\Requests\StoreUnitRequest;
use Modules\Units\Http\Requests\UpdateUnitRequest;

class UnitsController extends AccountBaseController

{
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'units::app.menu.units';
        $this->pageIcon  = 'ti-settings';
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(UnitsDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_unit');
        abort_403(!in_array($viewPermission, ['all','none']));

        $this->units      = Unit::all();
        $this->floors     = Floor::all();
        $this->towers     = Tower::all();
        $this->typeunits  = TypeUnit::all();
        $this->totalUnits = count($this->units);

        return $dataTable->render('units::units.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->pageTitle = __('units::app.menu.units');

        $this->floors    = Floor::all();
        $this->towers    = Tower::all();
        $this->typeunits = TypeUnit::all();

        if (request()->ajax()) {
            $html = view('units::units.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'units::units.ajax.create';

        return view('units::units.create', $this->data);
    }

    /**
     * @param StoreRequest $request
     * @return array
     */
    public function store(StoreUnitRequest $request)
    {
        // create new user
        $unit              = new Unit();
        $unit->unit_code   = $request->input('unit_code');
        $unit->unit_name   = $request->input('unit_name');
        $unit->floor_id    = $request->input('floor_id');
        $unit->tower_id    = $request->input('tower_id');
        $unit->typeunit_id = $request->input('typeunit_id');
        $unit->luas        = $request->input('luas');
        $unit->address     = $request->input('address');
        $unit->save();

        $redirectUrl = urldecode($request->redirect_url);
        if ($redirectUrl == '') {
            $redirectUrl = route('units.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $viewPermission = user()->permission('view_unit');
        if ($viewPermission == 'none') {
            $hasAccess = UsersUnit::where('user_id', user()->id)
                ->where('unit_id', $id)
                ->exists();
        
            abort_403(!$hasAccess);
        } else {
            abort_403(!in_array($viewPermission, ['all']));
        }

        $this->pageTitle = __('units::app.menu.units');
        $this->unit      = Unit::findOrFail($id);
        $this->floor     = Floor::where('id', '=', $this->unit->floor_id)->first();
        $this->tower     = Tower::where('id', '=', $this->unit->tower_id)->first();
        $this->typeunit  = TypeUnit::where('id', '=', $this->unit->typeunit_id)->first();

        if (request()->ajax()) {
            $html = view('units::units.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'units::units.ajax.show';
        return view('units::units.create', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $editPermission = user()->permission('edit_unit');
        if ($editPermission == 'none') {
            $hasAccess = UsersUnit::where('user_id', user()->id)
                ->where('unit_id', $id)
                ->exists();
        
            abort_403(!$hasAccess);
        } else {
            abort_403(!in_array($editPermission, ['all']));
        }

        $this->pageTitle = __('units::app.menu.units');
        $this->unit      = Unit::findOrFail($id);
        $this->floors    = Floor::all();
        $this->towers    = Tower::all();
        $this->typeunits = TypeUnit::all();

        if (request()->ajax()) {
            $html = view('units::units.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'units::units.ajax.edit';
        return view('units::units.create', $this->data);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(UpdateUnitRequest $request, $id)
    {
        $editUnit = user()->permission('edit_unit');
        abort_403($editUnit != 'all');

        $unit = Unit::find($id);
        $unit->unit_code   = $request->input('unit_code');
        $unit->unit_name   = $request->input('unit_name');
        $unit->floor_id    = $request->input('floor_id');
        $unit->tower_id    = $request->input('tower_id');
        $unit->typeunit_id = $request->input('typeunit_id');
        $unit->luas        = $request->input('luas');
        $unit->address     = $request->input('address');
        $unit->save();

        $redirectUrl = route('units.index');
        return Reply::successWithData(__('messages.unitUpdated'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_unit');
        abort_403($deletePermission != 'all');

        Unit::destroy($id);

        $redirectUrl = route('units.index');
        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => $redirectUrl]);
    }
}
