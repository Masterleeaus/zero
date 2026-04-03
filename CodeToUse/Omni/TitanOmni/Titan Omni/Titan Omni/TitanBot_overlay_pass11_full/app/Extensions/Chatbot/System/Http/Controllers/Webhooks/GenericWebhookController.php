<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Http\Controllers\Webhooks;

use App\Extensions\Chatbot\System\Enums\ChannelTypeEnum;
use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Services\Channels\ChannelWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class GenericWebhookController extends Controller
{
    public function __invoke(Chatbot $chatbot, string $channel, Request $request, ChannelWebhookService $service): JsonResponse
    {
        $result = $service->ingest($chatbot, ChannelTypeEnum::tryFrom($channel) ?? ChannelTypeEnum::Generic, $request);
        return response()->json($result, 202);
    }
}
