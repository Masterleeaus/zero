<?php

namespace Modules\Units\Http\Controllers;

use App\Helper\Reply;
use Modules\Units\Entities\Floor;
use App\Http\Controllers\AccountBaseController;
use Modules\Units\Http\Requests\StoreFloorRequest;
use Modules\Units\Http\Requests\UpdateFloorRequest;


class FloorsController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.floor';
        $this->activeSettingMenu = 'floor';
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('units::unit-settings.create-floor-modal');
    }

    /**
     * @param StoreFloor $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreFloorRequest $request)
    {
        $floor = new Floor();
        $floor->floor_code = $request->floor_code;
        $floor->floor_name = $request->floor_name;
        $floor->save();

        $allFloors = Floor::all();

        $select = '<option value="">--</option>';

        foreach ($allFloors as $floor) {
            $select .= '<option value="' . $floor->id . '">' . mb_ucwords($floor->floor_name) . '</option>';
        }

        return Reply::successWithData(__('messages.recordSaved'), ['optionData' => $select]);
    }

    /**
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $this->floor = Floor::findOrFail($id);
        return view('units::unit-settings.edit-floor-modal', $this->data);
    }

    /**
     * @param UpdateFloor $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateFloorRequest $request, $id)
    {
        $floor = Floor::findOrFail($id);
        $floor->floor_code = $request->floor_code;
        $floor->floor_name = $request->floor_name;
        $floor->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * @param int $id
     * @return array
     */
    public function destroy($id)
    {
        Floor::destroy($id);
        return Reply::success(__('messages.deleteSuccess'));
    }

    public function createModal()
    {
        return view('units::unit-settings.create-floor-modal');
    }

}
