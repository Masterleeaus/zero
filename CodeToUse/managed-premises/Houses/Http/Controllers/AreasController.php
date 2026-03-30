<?php

namespace Modules\Houses\Http\Controllers;

use App\Helper\Reply;
use Modules\Houses\Entities\Area;
use App\Http\Controllers\AccountBaseController;
use Modules\Houses\Http\Requests\StoreAreaRequest;
use Modules\Houses\Http\Requests\UpdateAreaRequest;


class AreasController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.area';
        $this->activeSettingMenu = 'area';
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('houses::house-settings.create-area-modal');
    }

    /**
     * @param StoreArea $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreAreaRequest $request)
    {
        $area = new Area();
        $area->area_code = $request->area_code;
        $area->area_name = $request->area_name;
        $area->save();

        $allAreas = Area::all();

        $select = '<option value="">--</option>';

        foreach ($allAreas as $area) {
            $select .= '<option value="' . $area->id . '">' . mb_ucwords($area->area_name) . '</option>';
        }

        return Reply::successWithData(__('messages.recordSaved'), ['optionData' => $select]);
    }

    /**
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $this->area = Area::findOrFail($id);
        return view('houses::house-settings.edit-area-modal', $this->data);
    }

    /**
     * @param UpdateArea $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateAreaRequest $request, $id)
    {
        $area = Area::findOrFail($id);
        $area->area_code = $request->area_code;
        $area->area_name = $request->area_name;
        $area->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * @param int $id
     * @return array
     */
    public function destroy($id)
    {
        Area::destroy($id);
        return Reply::success(__('messages.deleteSuccess'));
    }

    public function createModal()
    {
        return view('houses::house-settings.create-area-modal');
    }

}
