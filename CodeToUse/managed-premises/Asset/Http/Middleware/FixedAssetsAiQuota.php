<?php
namespace Modules\FixedAssets\Http\Middleware;
use Closure; use Illuminate\Http\Request;
class FixedAssetsAiQuota {
  public function handle(Request $request, Closure $next) {
    if (app()->bound('AICore\\Contracts\\QuotaInterface')) {
      $quota = app('AICore\\Contracts\\QuotaInterface');
      $tenantId = optional($request->user())->tenant_id ?? $request->get('tenant_id');
      if (!$quota->checkAndConsume('fixedassets.ai', $tenantId, ['cost'=>1])) {
        return response()->json(['ok'=>false,'error'=>'AI quota exceeded'], 429);
      }
    }
    return $next($request);
  }
}
