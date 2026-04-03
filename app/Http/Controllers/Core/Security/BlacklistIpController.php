<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Security;

use App\Http\Controllers\Controller;
use App\Models\Security\BlacklistIp;
use App\Services\Security\CyberSecurityConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * BlacklistIpController — CRUD for the IP address blacklist.
 *
 * Admin-only. All operations write to the system-wide blacklist_ips table.
 */
class BlacklistIpController extends Controller
{
    public function __construct(private readonly CyberSecurityConfigService $configService) {}

    public function index(): JsonResponse
    {
        $this->authorizeAdmin();

        return response()->json(BlacklistIp::orderBy('ip_address')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'ip_address' => 'required|ip|max:45',
        ]);

        $entry = $this->configService->addBlacklistIp($validated['ip_address']);

        return response()->json(['ok' => true, 'entry' => $entry], 201);
    }

    public function update(Request $request, BlacklistIp $blacklistIp): JsonResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'ip_address' => 'required|ip|max:45|unique:blacklist_ips,ip_address,' . $blacklistIp->id,
        ]);

        $blacklistIp->ip_address = $validated['ip_address'];
        $blacklistIp->save();

        return response()->json(['ok' => true, 'entry' => $blacklistIp]);
    }

    public function destroy(BlacklistIp $blacklistIp): JsonResponse
    {
        $this->authorizeAdmin();

        $blacklistIp->delete();

        return response()->json(['ok' => true]);
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
