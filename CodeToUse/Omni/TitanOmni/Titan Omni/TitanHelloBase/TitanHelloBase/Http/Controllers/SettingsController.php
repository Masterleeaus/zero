<?php

namespace Modules\TitanHello\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SettingsController extends Controller
{
    public function index()
    {
        $twilio = [
            'require_signature' => (bool) config('titanhello.twilio.require_signature'),
            'auth_token_set' => (bool) config('titanhello.twilio.auth_token'),
        ];

        return view('titanhello::settings.index', compact('twilio'));
    }

    public function save(Request $request)
    {
        return redirect()->back()->with('status', 'Settings are env-driven in Pass 1 (no DB writes yet).');
    }
}
