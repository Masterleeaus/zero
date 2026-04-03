<?php

namespace Modules\Inventory\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class NotificationService
{
    public function stocktakeFinalized(array $payload): void
    {
        $cfg = config('inventory.notifications', []);
        if (!($cfg['enabled'] ?? false)) return;

        $text = 'Stocktake finalized: #' . ($payload['stocktake_id'] ?? '')
              . "\n" . 'Adjustments: ' . json_encode($payload['adjustments'] ?? [], JSON_UNESCAPED_SLASHES);

        // Email
        $emails = array_filter(array_map('trim', explode(',', (string)($cfg['email_to'] ?? ''))));
        if ($emails) {
            try { Mail::raw($text, function($m) use ($emails){ $m->to($emails)->subject('Inventory: Stocktake Finalized'); }); } catch (\Throwable $e) {}
        }

        // Webhook
        $url = (string)($cfg['webhook_url'] ?? '');
        if ($url) {
            try { Http::asJson()->post($url, $payload); } catch (\Throwable $e) {}
        }
    }
}
