<?php

namespace App\Http\Controllers\Omni;

use App\Http\Controllers\Controller;
use App\Services\Omni\OmniAdapterRegistry;
use App\Services\Omni\OmniDualWriteManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OmniLegacyMirrorController extends Controller
{
    public function store(
        Request $request,
        string $driver,
        OmniAdapterRegistry $registry,
        OmniDualWriteManager $dualWrite
    ): JsonResponse {
        $adapter = $registry->for($driver);
        $payload = $adapter->mirrorToOmni($request->all());
        $result = $dualWrite->ingest($payload);

        return response()->json([
            'ok' => true,
            'driver' => $driver,
            'conversation_id' => $result['conversation']->id,
            'message_id' => $result['message']->id,
        ]);
    }
}
