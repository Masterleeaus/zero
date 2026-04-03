<?php

namespace App\Services\TitanZeroPwaSystem;

use Illuminate\Support\Facades\Config;

class TitanPwaManifestService
{
    public function __construct(
        private readonly PwaRuntimeContractService $contractService,
    ) {}

    /**
     * Return the full PWA web app manifest as an array.
     */
    public function manifest(): array
    {
        $appName = Config::get('app.name', 'TitanZero');

        return [
            'name'             => $appName,
            'short_name'       => Config::get('pwa.short_name', 'TitanZero'),
            'description'      => Config::get('pwa.description', 'AI-first service business operating system'),
            'start_url'        => Config::get('pwa.start_url', '/'),
            'display'          => Config::get('pwa.display', 'standalone'),
            'background_color' => Config::get('pwa.background_color', '#0f172a'),
            'theme_color'      => Config::get('pwa.theme_color', '#6366f1'),
            'orientation'      => Config::get('pwa.orientation', 'portrait-primary'),
            'scope'            => Config::get('pwa.scope', '/'),
            'icons'            => Config::get('pwa.icons', [
                [
                    'src'   => '/pwa/icons/icon-192.png',
                    'sizes' => '192x192',
                    'type'  => 'image/png',
                ],
                [
                    'src'   => '/pwa/icons/icon-512.png',
                    'sizes' => '512x512',
                    'type'  => 'image/png',
                    'purpose' => 'any maskable',
                ],
            ]),
            'categories'       => ['business', 'productivity'],
            'lang'             => Config::get('app.locale', 'en'),
        ];
    }

    /**
     * Return the client-side bootstrap config injected into the PWA shell.
     * Delegates to PwaRuntimeContractService for the rich contract.
     */
    public function bootstrapConfig(?int $userId = null, ?int $companyId = null): array
    {
        return $this->contractService->build($userId, $companyId);
    }
}
