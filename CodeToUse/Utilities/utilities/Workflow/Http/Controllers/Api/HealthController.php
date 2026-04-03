<?php

namespace Modules\Workflow\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class HealthController extends Controller
{
    public function ping(Request $request)
    {
        return response()->json([
            'ok' => true,
            'module' => 'workflow',
            'time' => now()->toIso8601String(),
        ]);
    }
}
