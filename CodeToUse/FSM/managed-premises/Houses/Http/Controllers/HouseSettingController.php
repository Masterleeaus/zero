<?php

namespace Modules\Houses\Http\Controllers;

use App\Models\User;
use App\Helper\Reply;
use Modules\Houses\Entities\Area;
use Modules\Houses\Entities\Tower;
use Modules\Houses\Entities\TypeHouse;
use App\Http\Controllers\AccountBaseController;

class HouseSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.houseSettings';
        $this->activeSettingMenu = 'house_settings';
        $this->middleware(function ($request, $next) {
            abort_403(!(user()->permission('add_house') == 'all'));
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $this->towers = Tower::all();
        $this->areas = Area::all();
        $this->typehouses = TypeHouse::all();


        $this->view = 'house-settings.ajax.typehouse';

        $tab = request('tab');

        switch ($tab) {
        case 'tower':
            $this->pageTitle = 'app.menu.towers';
            $this->view = 'houses::house-settings.ajax.tower';
            break;
        case 'area':
            $this->pageTitle = 'app.menu.areas';
            $this->view = 'houses::house-settings.ajax.area';
            break;
        default:
            $this->pageTitle = 'houses::app.menu.typehouse';
            $this->view = 'houses::house-settings.ajax.typehouse';
            break;
        }

        $this->activeTab = $tab ?: 'typehouse';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        return view('houses::house-settings.index', $this->data);

    }

}
