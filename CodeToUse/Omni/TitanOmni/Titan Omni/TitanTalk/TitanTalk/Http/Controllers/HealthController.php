<?php

namespace Modules\TitanTalk\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function ping(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'module' => 'TitanTalk',
            'time' => now()->toIso8601String(),
        ]);
    }
}
