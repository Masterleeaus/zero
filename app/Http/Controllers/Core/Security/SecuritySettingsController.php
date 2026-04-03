<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Security;

use App\Http\Controllers\Controller;
use App\Models\Security\BlacklistEmail;
use App\Models\Security\BlacklistIp;
use App\Models\Security\CyberSecurityConfig;
use App\Models\Security\LoginExpiry;
use App\Models\Security\SecurityAuditEvent;
use App\Services\Security\CyberSecurityConfigService;
use App\Services\Security\SecurityAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SecuritySettingsController
 *
 * Manages the singleton CyberSecurityConfig and provides an overview of
 * the security audit trail.  Admin-only (requires is_superadmin or admin role).
 */
class SecuritySettingsController extends Controller
{
    public function __construct(
        private readonly CyberSecurityConfigService $configService,
        private readonly SecurityAuditService $auditService,
    ) {}

    /** Display current security configuration. */
    public function index(): JsonResponse
    {
        $this->authorizeAdmin();

        return response()->json([
            'config'          => $this->configService->getConfig(),
            'blacklist_ips'   => BlacklistIp::count(),
            'blacklist_emails' => BlacklistEmail::count(),
            'login_expiries'  => LoginExpiry::count(),
        ]);
    }

    /** Update login-protection settings. */
    public function updateLoginProtection(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'max_retries'           => 'sometimes|integer|min:1|max:20',
            'lockout_time'          => 'sometimes|integer|min:1|max:60',
            'max_lockouts'          => 'sometimes|integer|min:1|max:20',
            'extended_lockout_time' => 'sometimes|integer|min:0|max:24',
            'reset_retries'         => 'sometimes|integer|min:1|max:168',
            'alert_after_lockouts'  => 'sometimes|integer|min:0|max:20',
            'email'                 => 'sometimes|nullable|email|max:320',
            'user_timeout'          => 'sometimes|integer|min:1|max:1440',
            'ip_check'              => 'sometimes|boolean',
            'ip'                    => 'sometimes|nullable|ip',
        ]);

        $config = $this->configService->updateLoginProtection($validated);

        return response()->json(['ok' => true, 'config' => $config]);
    }

    /** Toggle unique-session enforcement. */
    public function updateSessionPolicy(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'unique_session' => 'required|boolean',
        ]);

        $config = $this->configService->updateSessionPolicy((bool) $validated['unique_session']);

        return response()->json(['ok' => true, 'unique_session' => $config->unique_session]);
    }

    /** Return the recent security audit trail for the current tenant. */
    public function auditTrail(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $companyId = $request->user()?->company_id;

        if (! $companyId) {
            return response()->json(['ok' => false, 'error' => 'Tenancy required'], 403);
        }

        $limit  = (int) $request->query('limit', 50);
        $events = $this->auditService->recentForCompany($companyId, min($limit, 200));

        return response()->json(['ok' => true, 'events' => $events]);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(
            auth()->check() && (auth()->user()?->is_superadmin || auth()->user()?->isAdmin()),
            403,
            'Admin access required.',
        );
    }
}
