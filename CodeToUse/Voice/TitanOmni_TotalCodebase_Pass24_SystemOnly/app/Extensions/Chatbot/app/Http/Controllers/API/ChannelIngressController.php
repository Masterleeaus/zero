<?php

namespace App\Extensions\Chatbot\App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Extensions\Chatbot\App\Services\Ingestion\ConnectedExtensionIngressService;

class ChannelIngressController extends Controller
{
    public function __construct(protected ConnectedExtensionIngressService $ingress)
    {
    }

    public function ingest(string $channel)
    {
        return response()->json(
            $this->ingress->ingest($channel, request()->all())
        );
    }
}
