<?php

namespace Modules\Units\Http\Controllers;

use App\Helper\Reply;
use Modules\Units\Entities\TypeUnit;
use App\Http\Controllers\AccountBaseController;
use Modules\Units\Http\Requests\StoreTypeUnitRequest;
use Modules\Units\Http\Requests\UpdateTypeUnitRequest;


class TypeUnitsController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.typeunit';
        $this->activeSettingMenu = 'typeunit';
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('units::unit-settings.create-typeunit-modal');
    }

    /**
     * @param StoreTypeUnit $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreTypeUnitRequest $request)
    {
        $typeunit = new TypeUnit();
        $typeunit->typeunit_code = $request->typeunit_code;
        $typeunit->typeunit_name = $request->typeunit_name;
        $typeunit->save();

        $allTypeUnits = TypeUnit::all();

        $select = '<option value="">--</option>';

        foreach ($allTypeUnits as $typeunit) {
            $select .= '<option value="' . $typeunit->id . '">' . mb_ucwords($typeunit->typeunit_name) . '</option>';
        }

        return Reply::successWithData(__('messages.recordSaved'), ['optionData' => $select]);
    }

    /**
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $this->typeunit = TypeUnit::findOrFail($id);
        return view('units::unit-settings.edit-typeunit-modal', $this->data);
    }

    /**
     * @param UpdateTypeUnit $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateTypeUnitRequest $request, $id)
    {
        $typeunit = TypeUnit::findOrFail($id);
        $typeunit->typeunit_code = $request->typeunit_code;
        $typeunit->typeunit_name = $request->typeunit_name;
        $typeunit->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * @param int $id
     * @return array
     */
    public function destroy($id)
    {
        TypeUnit::destroy($id);
        return Reply::success(__('messages.deleteSuccess'));
    }

    public function createModal()
    {
        return view('units::unit-settings.create-typeunit-modal');
    }

}
