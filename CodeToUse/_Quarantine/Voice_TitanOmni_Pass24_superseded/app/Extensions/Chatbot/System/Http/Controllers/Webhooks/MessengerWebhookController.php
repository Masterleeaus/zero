<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Http\Controllers\Webhooks;

use App\Extensions\Chatbot\System\Enums\ChannelTypeEnum;
use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Services\Channels\ChannelWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MessengerWebhookController extends Controller
{
    public function __invoke(Chatbot $chatbot, Request $request, ChannelWebhookService $service): JsonResponse
    {
        return response()->json($service->ingest($chatbot, ChannelTypeEnum::Messenger, $request), 202);
    }
}
