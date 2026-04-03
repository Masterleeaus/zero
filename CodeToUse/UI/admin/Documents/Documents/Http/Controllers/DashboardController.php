<?php

namespace Modules\Documents\Http\Controllers;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('documents::dashboard.index');
    }
}
