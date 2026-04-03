<?php

namespace App\Extensions\Chatbot\App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Extensions\Chatbot\App\Services\Channels\ChannelBridgeRegistry;

class ChannelBridgeHealthController extends Controller
{
    public function __construct(protected ChannelBridgeRegistry $registry)
    {
    }

    public function index()
    {
        return response()->json([
            'bridges' => $this->registry->all(),
        ]);
    }
}
