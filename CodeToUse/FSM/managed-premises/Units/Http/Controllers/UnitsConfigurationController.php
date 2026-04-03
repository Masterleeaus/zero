<?php

namespace Modules\Units\Http\Controllers;

use Exception;
use App\Helper\Reply;
use App\Models\BaseModel;
use Illuminate\Http\Request;
use Modules\Units\Entities\UsersUnit;
use Modules\Units\DataTables\UnitsConfigurationDataTable;
use App\Http\Controllers\AccountBaseController;
use App\Models\User;
use Modules\Units\Entities\Unit;

class UnitsConfigurationController extends AccountBaseController

{
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'units::app.menu.unitCog';
        $this->pageIcon  = 'ti-settings';
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(UnitsConfigurationDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_unit');
        abort_403(!in_array($viewPermission, ['all']));

        return $dataTable->render('units::units-configuration.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $addPermission = user()->permission('add_unit');
        abort_403(!in_array($addPermission, ['all']));

        $this->pageTitle = __('units::app.menu.unitCog');
        $this->units     = Unit::all();
        $this->users     = User::all();

        if (request()->ajax()) {
            $html = view('units::units-configuration.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'units::units-configuration.ajax.create';

        return view('units::units-configuration.create', $this->data);
    }

    /**
     * @param StoreRequest $request
     * @return array
     */
    public function store(Request $request)
    {
        if (empty($request->user_id)) {
            return Reply::error(__('messages.addItem'));
        }

        foreach ($request->unit_id as $unitId) {
            if (is_null($unitId)) {
                return Reply::error(__('messages.addItem'));
            }
        }

        $unit_id = request()->unit_id;
        $user_id = request()->user_id;

        foreach ($unit_id as $key => $unit) {
            $units             = new UsersUnit();
            $units->company_id = company()->id;
            $units->unit_id    = $unit;
            $units->user_id    = $user_id;
            $units->saveQuietly();
        }

        $redirectUrl = urldecode($request->redirect_url);
        if ($redirectUrl == '') {
            $redirectUrl = route('units-configuration.index');
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
        abort_403(!in_array($viewPermission, ['all']));

        $this->pageTitle = __('units::app.menu.unitCog');
        $this->units     = UsersUnit::with('unit.floor', 'unit.tower', 'unit.typeunit', 'user')->get();
        $this->user      = User::where('id', '=', $id)->first();

        if (request()->ajax()) {
            $html = view('units::units-configuration.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'units::units-configuration.ajax.show';
        return view('units::units-configuration.create', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $editPermission = user()->permission('edit_unit');
        abort_403(!in_array($editPermission, ['all']));

        $this->pageTitle   = __('units::app.menu.unitCog');
        $this->users_units = UsersUnit::where('user_id', '=', $id)->get();
        $this->user_id     = $id;
        $this->units       = Unit::all();
        $this->users       = User::all();

        if (request()->ajax()) {
            $html = view('units::units-configuration.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'units::units-configuration.ajax.edit';
        return view('units::units-configuration.create', $this->data);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $user_id)
    {
        $editUnit = user()->permission('edit_unit');
        abort_403($editUnit != 'all');

        $items_ids = $request->input('item_ids');
        $unit_id   = $request->input('unit_id');

        UsersUnit::where('user_id', $user_id)->whereNotIn('id', $items_ids)->delete();

        foreach ($unit_id as $key => $unit) {
            $item_id = isset($items_ids[$key]) ? $items_ids[$key] : 0;

            try {
                $units = UsersUnit::findOrFail($item_id);
            } catch (Exception $e) {
                $units = new UsersUnit();
            }

            $units->company_id = company()->id;
            $units->unit_id    = $unit;
            $units->user_id    = $user_id;
            $units->saveQuietly();
        }

        $redirectUrl = urldecode($request->redirect_url);
        if ($redirectUrl == '') {
            $redirectUrl = route('units-configuration.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($user_id)
    {
        $deletePermission = user()->permission('delete_unit');
        abort_403($deletePermission != 'all');

        UsersUnit::where('user_id', $user_id)->delete();

        $redirectUrl = route('units-configuration.index');
        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => $redirectUrl]);
    }
}
