<?php
namespace Modules\TitanHello\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PolicyGuard
{
    public function handle(Request $request, Closure $next)
    {
        $text = (string) ($request->input('message') ?? $request->input('text') ?? '');

        // Very light demo guard: redact simple PII patterns and block obvious profanity
        $piiPatterns = [
            '/\b\d{3,4}[-\s]?\d{3}[-\s]?\d{3,4}\b/' => '[redacted-phone]',
            '/[\w\.\-]+@[\w\-]+\.[A-Za-z]{2,}/' => '[redacted-email]',
        ];
        foreach ($piiPatterns as $rx => $mask) {
            $text = preg_replace($rx, $mask, $text);
        }

        $bad = ['damn','shit','fuck']; // demo only; replace with proper wordlist
        $flagged = false;
        foreach ($bad as $w) {
            if (stripos($text, $w) !== false) { $flagged = true; break; }
        }

        // Replace request text if we redacted
        if ($request->has('message')) $request->merge(['message' => $text]);
        if ($request->has('text')) $request->merge(['text' => $text]);

        if ($flagged) {
            return response()->json(['refused' => true, 'reason' => 'policy_violation', 'text' => 'Sorry, can’t help with that.'], 200);
        }

        return $next($request);
    }
}
