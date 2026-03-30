<?php

namespace Modules\Houses\Http\Controllers;

use App\Helper\Reply;
use Modules\Houses\Entities\TypeHouse;
use App\Http\Controllers\AccountBaseController;
use Modules\Houses\Http\Requests\StoreTypeHouseRequest;
use Modules\Houses\Http\Requests\UpdateTypeHouseRequest;


class TypeHousesController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.typehouse';
        $this->activeSettingMenu = 'typehouse';
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('houses::house-settings.create-typehouse-modal');
    }

    /**
     * @param StoreTypeHouse $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreTypeHouseRequest $request)
    {
        $typehouse = new TypeHouse();
        $typehouse->typehouse_code = $request->typehouse_code;
        $typehouse->typehouse_name = $request->typehouse_name;
        $typehouse->save();

        $allTypeHouses = TypeHouse::all();

        $select = '<option value="">--</option>';

        foreach ($allTypeHouses as $typehouse) {
            $select .= '<option value="' . $typehouse->id . '">' . mb_ucwords($typehouse->typehouse_name) . '</option>';
        }

        return Reply::successWithData(__('messages.recordSaved'), ['optionData' => $select]);
    }

    /**
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $this->typehouse = TypeHouse::findOrFail($id);
        return view('houses::house-settings.edit-typehouse-modal', $this->data);
    }

    /**
     * @param UpdateTypeHouse $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateTypeHouseRequest $request, $id)
    {
        $typehouse = TypeHouse::findOrFail($id);
        $typehouse->typehouse_code = $request->typehouse_code;
        $typehouse->typehouse_name = $request->typehouse_name;
        $typehouse->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * @param int $id
     * @return array
     */
    public function destroy($id)
    {
        TypeHouse::destroy($id);
        return Reply::success(__('messages.deleteSuccess'));
    }

    public function createModal()
    {
        return view('houses::house-settings.create-typehouse-modal');
    }

}
