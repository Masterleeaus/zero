<?php

namespace Modules\TitanTalk\Http\Controllers;

use Illuminate\Routing\Controller;

class TitanTalkController extends Controller
{
    public function index()
    {
        return view('titantalk::index');
    }

    public function conversations()
    {
        return view('titantalk::conversations');
    }

    public function settings()
    {
        return view('titantalk::settings');
    }
}
