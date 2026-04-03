<?php

namespace Modules\TitanHello\Http\Controllers;

use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('titanhello::dashboard.index');
    }
}
