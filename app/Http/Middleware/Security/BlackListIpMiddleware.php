<?php

declare(strict_types=1);

namespace App\Http\Middleware\Security;

use App\Models\Security\BlacklistIp;
use App\Services\Security\SecurityAuditService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Block requests whose source IP is on the blacklist.
 *
 * Fires a security audit event so the block is visible in the
 * tenant's security audit trail.
 */
class BlackListIpMiddleware
{
    public function __construct(private readonly SecurityAuditService $audit) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (BlacklistIp::where('ip_address', $request->ip())->exists()) {
            $this->audit->record(
                \App\Models\Security\SecurityAuditEvent::TYPE_IP_BLOCKED,
                ['ip' => $request->ip(), 'path' => $request->path()],
                $request->ip(),
            );

            abort(403, 'Access denied: IP address is blocked.');
        }

        return $next($request);
    }
}
