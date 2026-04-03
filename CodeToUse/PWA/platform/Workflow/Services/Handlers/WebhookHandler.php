<?php

namespace Modules\Workflow\Services\Handlers;

use Modules\Workflow\Entities\WorkflowRunStep;
use Illuminate\Support\Facades\Http;

class WebhookHandler
{
    public function handle(WorkflowRunStep $step): void
    {
        $url = $step->config['url'] ?? null;
        if (!$url) throw new \InvalidArgumentException('Webhook url missing');
        Http::post($url, $step->config['payload'] ?? []);
    }
}
