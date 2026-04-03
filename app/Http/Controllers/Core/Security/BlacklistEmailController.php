<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Security;

use App\Http\Controllers\Controller;
use App\Models\Security\BlacklistEmail;
use App\Services\Security\CyberSecurityConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * BlacklistEmailController — CRUD for the email / email-domain blacklist.
 *
 * Admin-only. Accepts full email addresses or @domain.tld prefix entries.
 */
class BlacklistEmailController extends Controller
{
    public function __construct(private readonly CyberSecurityConfigService $configService) {}

    public function index(): JsonResponse
    {
        $this->authorizeAdmin();

        return response()->json(BlacklistEmail::orderBy('email')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'max:320',
                'unique:blacklist_emails,email',
                static function (string $attribute, mixed $value, \Closure $fail) {
                    // Accept full emails or @domain.tld prefix entries.
                    // For domain prefixes: disallow consecutive dots, leading/trailing dots in domain part.
                    if (! filter_var($value, FILTER_VALIDATE_EMAIL) && ! preg_match('/^@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/', $value)) {
                        $fail('The ' . $attribute . ' must be a valid email address or @domain.tld prefix.');
                    }
                },
            ],
        ]);

        $entry = $this->configService->addBlacklistEmail($validated['email']);

        return response()->json(['ok' => true, 'entry' => $entry], 201);
    }

    public function update(Request $request, BlacklistEmail $blacklistEmail): JsonResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'email' => 'required|string|max:320|unique:blacklist_emails,email,' . $blacklistEmail->id,
        ]);

        $blacklistEmail->email = $validated['email'];
        $blacklistEmail->save();

        return response()->json(['ok' => true, 'entry' => $blacklistEmail]);
    }

    public function destroy(BlacklistEmail $blacklistEmail): JsonResponse
    {
        $this->authorizeAdmin();

        $blacklistEmail->delete();

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
