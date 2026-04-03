<?php

namespace Modules\Workflow\Services;

use Modules\Workflow\Services\Handlers\WebhookHandler;
use Modules\Workflow\Services\Handlers\NotifyHandler;
use Modules\Workflow\Services\Handlers\CustomerConnectSendHandler;
use Modules\Workflow\Services\Handlers\DocumentsCreatePackHandler;
use Modules\Workflow\Services\Handlers\TitanZeroIntentHandler;
use Modules\Workflow\Services\Handlers\AssetManagerMaintenanceHandler;
use Modules\Workflow\Services\Handlers\CreateTaskHandler;

class ActionRegistry
{
    public static function all(): array
    {
        return [
            ['key' => 'notify', 'label' => 'Notify (stub)', 'handler' => NotifyHandler::class],
            ['key' => 'webhook', 'label' => 'Webhook POST', 'handler' => WebhookHandler::class],

            // System integrations (governed)
            ['key' => 'customerconnect.send', 'label' => 'Send via CustomerConnect', 'handler' => CustomerConnectSendHandler::class],
            ['key' => 'documents.pack', 'label' => 'Create/Attach Documents Pack', 'handler' => DocumentsCreatePackHandler::class],
            ['key' => 'titanzero.intent', 'label' => 'Run via Titan Zero (structured intent)', 'handler' => TitanZeroIntentHandler::class],
            ['key' => 'assetmanager.maintenance', 'label' => 'Asset maintenance action', 'handler' => AssetManagerMaintenanceHandler::class],
            ['key' => 'core.task.create', 'label' => 'Create core Task', 'handler' => CreateTaskHandler::class],
        ];
    }

    public static function handlerFor(string $key): ?string
    {
        foreach (self::all() as $a) {
            if ($a['key'] === $key) return $a['handler'];
        }
        return null;
    }
}
