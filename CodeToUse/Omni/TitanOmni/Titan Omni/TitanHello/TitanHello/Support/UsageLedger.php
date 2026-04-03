<?php
namespace Modules\TitanHello\Support;

use Modules\TitanHello\Models\ProviderLog;

class UsageLedger {
    public static function record(string $feature, int $inTokensOrBytes, int $outTokensOrBytes, array $meta = []): void {
        // Save a lightweight provider log row (no tenant context here; controller can add more in the future)
        try {
            ProviderLog::create([
                'tenant_id' => $meta['tenant_id'] ?? null,
                'conversation_id' => $meta['conversation_id'] ?? null,
                'direction' => $meta['direction'] ?? 'response',
                'payload' => ['feature'=>$feature, 'in'=>$inTokensOrBytes, 'out'=>$outTokensOrBytes],
                'meta' => $meta,
            ]);
        } catch (\Throwable $e) {
            // swallow to avoid user-impacting failures
        }
    }
}
