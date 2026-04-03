<?php

namespace Modules\TrPackage\Http\Controllers;

use App\Helper\Reply;
use Modules\TrPackage\Entities\Ekspedisi;
use App\Http\Controllers\AccountBaseController;
use Modules\TrPackage\Http\Requests\StoreEkspedisi;

class EkspedisiController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle         = 'trpackage::modules.ekspedisi.ekspedisi';
        $this->activeSettingMenu = 'ekspedisi';
    }

      /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->ekspedisi = Ekspedisi::all();
        return view('trpackage::penerimaan.create-ekspedisi-modal', $this->data);
    }

      /**
     * @param StoreFloor $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreEkspedisi $request)
    {
        $ekspedisi             = new Ekspedisi();
        $ekspedisi->name        = $request->name;
        $ekspedisi->save();
        $allEkspedisi = Ekspedisi::all();

        $select = '<option value="">--</option>';
        foreach ($allEkspedisi as $item) {
            $select .= '<option value="' . $item->id . '">' . mb_ucwords($item->name) . '</option>';
        }

        return Reply::successWithData(__('trpackage::messages.addEkspedisi'), ['optionData' => $select]);
    }

      /**
     * @param UpdateFloor $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(StoreEkspedisi $request, $id)
    {
        $ekspedisi            = Ekspedisi::findOrFail($id);
        $ekspedisi->name       = $request->name ? strip_tags($request->name) : $ekspedisi->name;
        $ekspedisi->save();
        $allEkspedisi = Ekspedisi::all();
        $select = '<option value="">--</option>';
        foreach ($allEkspedisi as $item) {
            $select .= '<option value="' . $item->id . '">' . mb_ucwords($item->name) . '</option>';
        }

        return Reply::successWithData(__('trpackage::messages.updateEkspedisi'), ['optionData' => $select]);
    }

      /**
     * @param int $id
     * @return array
     */
    public function destroy($id)
    {
        Ekspedisi::destroy($id);
        return Reply::success(__('trpackage::messages.deleteEkspedisi'));
    }
}
