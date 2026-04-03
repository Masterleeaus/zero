<?php

namespace Modules\TitanHello\Http\Controllers;

use Illuminate\Routing\Controller;

class TitanHelloController extends Controller
{
    public function index()
    {
        return redirect()->route('titanhello.calls.index');
    }
}
