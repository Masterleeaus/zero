<?php

namespace Modules\Units\Http\Controllers;

use App\Models\User;
use App\Helper\Reply;
use Modules\Units\Entities\Floor;
use Modules\Units\Entities\Tower;
use Modules\Units\Entities\TypeUnit;
use App\Http\Controllers\AccountBaseController;

class UnitSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.unitSettings';
        $this->activeSettingMenu = 'unit_settings';
        $this->middleware(function ($request, $next) {
            abort_403(!(user()->permission('add_unit') == 'all'));
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
        $this->floors = Floor::all();
        $this->typeunits = TypeUnit::all();


        $this->view = 'unit-settings.ajax.typeunit';

        $tab = request('tab');

        switch ($tab) {
        case 'tower':
            $this->pageTitle = 'app.menu.towers';
            $this->view = 'units::unit-settings.ajax.tower';
            break;
        case 'floor':
            $this->pageTitle = 'app.menu.floors';
            $this->view = 'units::unit-settings.ajax.floor';
            break;
        default:
            $this->pageTitle = 'units::app.menu.typeunit';
            $this->view = 'units::unit-settings.ajax.typeunit';
            break;
        }

        $this->activeTab = $tab ?: 'typeunit';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        return view('units::unit-settings.index', $this->data);

    }

}
