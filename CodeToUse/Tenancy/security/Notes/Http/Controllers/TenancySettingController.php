<?php

namespace Modules\TrNotes\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Modules\TrNotes\Entities\Notes;

class TenancySettingController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle         = 'trnotes::modules.tenancySet';
        $this->activeSettingMenu = 'tenancy_settings';
        $this->middleware(function ($request, $next) {
            abort_403(!(user()->permission('add_notes') == 'all'));
            return $next($request);
        });
    }

    public function index()
    {
        $this->notes       = Notes::all();
        $this->view        = 'trnotes::tenancy-settings.ajax.notes';

        $tab = request('tab');
        switch ($tab) {
            // case 'pnl':
            //     $this->view = 'trnotes::tenancy-settings.ajax.pnl';
            //     break;
            default:
                $this->view = 'trnotes::tenancy-settings.ajax.notes';
                break;
        }

        $this->activeTab = $tab ?: 'notes';
        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        return view('trnotes::tenancy-settings.index', $this->data);
    }
}
