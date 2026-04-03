<?php

namespace Modules\TrNotes\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use Modules\TrNotes\Entities\Notes;
use Modules\TrNotes\Http\Requests\StoreNotes;
use App\Http\Controllers\AccountBaseController;

class NotesController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle         = 'trnotes::app.menu.notes';
        $this->activeSettingMenu = 'notes';
    }

      /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->notes = Notes::all();
        return view('trnotes::tenancy-settings.create-notes-modal', $this->data);
    }

      /**
     * @param StoreFloor $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreNotes $request)
    {
        $notes              = new Notes();
        $notes->module_name = $request->module_name;
        $notes->table_name  = $request->table_name;
        $notes->remark      = $request->remark;
        $notes->save();
        $all = Notes::all();

        $select = '<option value="">--</option>';
        foreach ($all as $balance) {
            $select .= '<option value="' . $balance->id . '">' . mb_ucwords($balance->module_name) . '</option>';
        }

        return Reply::successWithData(__('trnotes::messages.addNotes'), ['optionData' => $select]);
    }

      /**
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $this->floor = Notes::findOrFail($id);
        return view('trnotes::tenancy-settings.edit-floor-modal', $this->data);
    }

      /**
     * @param UpdateFloor $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(Request $request, $id)
    {
        $notes              = Notes::findOrFail($id);
        $notes->remark      = $request->remark ? strip_tags($request->remark) : $notes->remark;
        $notes->module_name = $request->module_name ? $request->module_name : $notes->module_name;
        $notes->table_name  = $request->table_name ? $request->table_name : $notes->table_name;
        $notes->save();

        return Reply::success(__('trnotes::messages.updateNotes'));
    }

      /**
     * @param int $id
     * @return array
     */
    public function destroy($id)
    {
        Notes::destroy($id);
        return Reply::success(__('trnotes::messages.deleteNotes'));
    }
}
