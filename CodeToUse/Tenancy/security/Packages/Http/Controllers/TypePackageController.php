<?php

namespace Modules\TrPackage\Http\Controllers;

use App\Helper\Reply;
use Modules\TrPackage\Entities\TypePackage;
use App\Http\Controllers\AccountBaseController;
use Modules\TrPackage\Http\Requests\StoreTypePackage;

class TypePackageController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle         = 'trpackage::modules.TypePackage.TypePackage';
        $this->activeSettingMenu = 'TypePackage';
    }

      /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->typePackage = TypePackage::all();
        return view('trpackage::penerimaan.create-type-modal', $this->data);
    }

      /**
     * @param StoreFloor $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreTypePackage $request)
    {
        $TypePackage             = new TypePackage();
        $TypePackage->name       = $request->name;
        $TypePackage->save();
        $allTypePackage = TypePackage::all();

        $select = '<option value="">--</option>';
        foreach ($allTypePackage as $item) {
            $select .= '<option value="' . $item->id . '">' . mb_ucwords($item->name) . '</option>';
        }

        return Reply::successWithData(__('trpackage::messages.addTypePackage'), ['optionData' => $select]);
    }

      /**
     * @param UpdateFloor $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(StoreTypePackage $request, $id)
    {
        $TypePackage            = TypePackage::findOrFail($id);
        $TypePackage->name       = $request->name ? strip_tags($request->name) : $TypePackage->name;
        $TypePackage->save();
        $allTypePackage = TypePackage::all();
        $select = '<option value="">--</option>';
        foreach ($allTypePackage as $item) {
            $select .= '<option value="' . $item->id . '">' . mb_ucwords($item->name) . '</option>';
        }

        return Reply::successWithData(__('trpackage::messages.updateTypePackage'), ['optionData' => $select]);
    }

      /**
     * @param int $id
     * @return array
     */
    public function destroy($id)
    {
        TypePackage::destroy($id);
        $allTypePackage = TypePackage::all();
        $select = '<option value="">--</option>';
        foreach ($allTypePackage as $item) {
            $select .= '<option value="' . $item->id . '">' . mb_ucwords($item->name) . '</option>';
        }
        return Reply::successWithData(__('trpackage::messages.deleteTypePackage'), ['optionData' => $select]);
    }
}
