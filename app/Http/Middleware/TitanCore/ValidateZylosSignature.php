<?php

namespace App\Http\Middleware\TitanCore;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ValidateZylosSignature — rejects unsigned Zylos skill runtime callbacks.
 */
class ValidateZylosSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $rawBody   = $request->getContent();
        $signature = (string) $request->header('X-Zylos-Signature', '');
        $secret    = (string) config('titan_core.zylos.secret', '');

        if ($secret === '' || $signature === '') {
            return response()->json(['ok' => false, 'error' => 'Signature required'], 401);
        }

        $expected = hash_hmac('sha256', $rawBody, $secret);

        if (! hash_equals($expected, $signature)) {
            return response()->json(['ok' => false, 'error' => 'Invalid Zylos signature'], 401);
        }

        return $next($request);
    }
}
