<?php

namespace Modules\Workflow\Services\Diagnostics;

class IntegrationHealth
{
    public function report(): array
    {
        return [
            'customerconnect' => class_exists('Modules\CustomerConnect\Providers\CustomerConnectServiceProvider'),
            'documents' => class_exists('Modules\Documents\Providers\DocumentsServiceProvider'),
            'titanzero' => class_exists('Modules\TitanZero\Providers\TitanZeroServiceProvider'),
            'assetmanager' => class_exists('Modules\AssetManager\Providers\AssetManagerServiceProvider'),
            'webhooks' => class_exists('Modules\Webhooks\Providers\WebhooksServiceProvider'),
        ];
    }
}
