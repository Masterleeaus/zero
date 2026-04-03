<?php

namespace Modules\Houses\Http\Controllers;

use App\Helper\Reply;
use App\Models\BaseModel;
use Illuminate\Http\Request;
use Modules\Houses\Entities\House;
use Modules\Houses\Entities\Area;
use Modules\Houses\Entities\Tower;
use Modules\Houses\Entities\TypeHouse;
use Modules\Houses\DataTables\HousesDataTable;
use App\Http\Controllers\AccountBaseController;
use Modules\Houses\Http\Requests\StoreHouseRequest;



class HousesController extends AccountBaseController

{
    public $arr = [];

    public function __construct() {
        parent::__construct();
        $this->pageTitle = 'houses::app.menu.houses';
        $this->pageIcon = 'ti-settings';
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(HousesDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_house');
        abort_403(!in_array($viewPermission, ['all']));

        $this->houses = House::all();
        $this->areas = Area::all();
        $this->towers = Tower::all();
        $this->typehouses = TypeHouse::all();
        $this->totalHouses = count($this->houses);

        return $dataTable->render('houses::houses.index', $this->data);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->pageTitle = __('houses::app.menu.houses');

        $this->areas = Area::all();
        $this->towers = Tower::all();
        $this->typehouses = TypeHouse::all();


        if (request()->ajax()) {
            $html = view('houses::houses.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'houses::houses.ajax.create';

        return view('houses::houses.ajax.create', $this->data);
    }

   /**
     * @param StoreRequest $request
     * @return array
     */
    public function store(StoreHouseRequest $request)
    {
        // create new user
        $house = new House();
        $house->house_code = $request->input('house_code');
        $house->house_name = $request->input('house_name');
        $house->area_id = $request->input('area_id');
        $house->tower_id = $request->input('tower_id');
        $house->typehouse_id = $request->input('typehouse_id');
        $house->luas = $request->input('luas');

        $house->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('houses.index');
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
        $this->pageTitle = __('houses::app.menu.houses');

        $this->house = House::findOrFail($id);
        $this->area = Area::where('id', '=', $this->house->area_id)->first();
        $this->tower = Tower::where('id', '=', $this->house->tower_id)->first();
        $this->typehouse = TypeHouse::where('id', '=', $this->house->typehouse_id)->first();

        if (request()->ajax())
        {
            $html = view('houses::houses.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'houses::houses.ajax.show';
        return view('houses::houses.create', $this->data);

    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $editPermission = user()->permission('edit_houses');

        $this->pageTitle = __('houses::app.menu.houses');

        $this->house = House::findOrFail($id);
        $this->areas = Area::all();
        $this->towers = Tower::all();
        $this->typehouses = TypeHouse::all();

        if (request()->ajax())
        {
            $html = view('houses::houses.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'houses::houses.ajax.edit';
        return view('houses::houses.create', $this->data);

    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $editHouse = user()->permission('edit_house');
        abort_403($editHouse != 'all');

        $house = House::find($id);
        // $house->house_code = $request->input('house_code');
        $house->house_name = $request->input('house_name');
        $house->area_id = $request->input('area_id');
        $house->tower_id = $request->input('tower_id');
        $house->typehouse_id = $request->input('typehouse_id');
        $house->luas = $request->input('luas');

        $house->save();

        $redirectUrl = route('houses.index');
        return Reply::successWithData(__('messages.houseUpdated'), ['redirectUrl' => $redirectUrl]);

    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_house');
        abort_403($deletePermission != 'all');

        House::destroy($id);

        $redirectUrl = route('houses.index');
        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => $redirectUrl]);
    }


}
