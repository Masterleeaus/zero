<?php

namespace Modules\Workflow\Http\Controllers;
use Illuminate\Routing\Controller as BaseController;
class DashboardController extends BaseController {
    public function index() { return view('workflow::dashboard.index'); }
}
