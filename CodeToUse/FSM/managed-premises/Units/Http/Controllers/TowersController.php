<?php

namespace Modules\Units\Http\Controllers;

use App\Helper\Reply;
use Modules\Units\Entities\Tower;
use App\Http\Controllers\AccountBaseController;
use Modules\Units\Http\Requests\StoreTowerRequest;
use Modules\Units\Http\Requests\UpdateTowerRequest;


class TowersController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.tower';
        $this->activeSettingMenu = 'tower';
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('units::unit-settings.create-tower-modal');
    }

    /**
     * @param StoreTower $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreTowerRequest $request)
    {
        $tower = new Tower();
        $tower->tower_code = $request->tower_code;
        $tower->tower_name = $request->tower_name;
        $tower->save();

        $allTowers = Tower::all();

        $select = '<option value="">--</option>';

        foreach ($allTowers as $tower) {
            $select .= '<option value="' . $tower->id . '">' . mb_ucwords($tower->tower_name) . '</option>';
        }

        return Reply::successWithData(__('messages.recordSaved'), ['optionData' => $select]);
    }

    /**
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $this->tower = Tower::findOrFail($id);
        return view('units::unit-settings.edit-tower-modal', $this->data);
    }

    /**
     * @param UpdateTower $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateTowerRequest $request, $id)
    {
        $tower = Tower::findOrFail($id);
        $tower->tower_code = $request->tower_code;
        $tower->tower_name = $request->tower_name;
        $tower->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * @param int $id
     * @return array
     */
    public function destroy($id)
    {
        Tower::destroy($id);
        return Reply::success(__('messages.deleteSuccess'));
    }

    public function createModal()
    {
        return view('units::unit-settings.create-tower-modal');
    }

}
