<?php

namespace Modules\TitanHello\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function index()
    {
        $checks = [
            'routes_loaded' => true,
            'twilio_account_sid_set' => (bool) config('titanhello.twilio.account_sid'),
            'twilio_auth_token_set' => (bool) config('titanhello.twilio.auth_token'),
            'twilio_default_from_set' => (bool) config('titanhello.twilio.default_from'),
        ];

        return view('titanhello::health.index', compact('checks'));
    }

    public function ping(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'module' => 'TitanHello',
            'time' => now()->toIso8601String(),
        ]);
    }
}
