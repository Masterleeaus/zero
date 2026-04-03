<?php

namespace Extensions\TitanHello\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyTwilioSignature
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('titan-hello.security.verify_twilio_signature', true)) {
            return $next($request);
        }

        $authToken = (string) (config('titan-hello.twilio.auth_token') ?? '');
        if ($authToken === '') {
            if (!app()->isLocal()) {
                return response('Twilio auth token not configured', 500);
            }
            return $next($request);
        }

        $twilioSignature = (string) $request->header('X-Twilio-Signature', '');
        if ($twilioSignature === '') {
            return response('Missing X-Twilio-Signature', 403);
        }

        $url = $request->fullUrl();
        $params = $request->all();
        ksort($params);
        $data = $url;
        foreach ($params as $k => $v) {
            if (is_array($v)) {
                $v = json_encode($v);
            }
            $data .= $k . $v;
        }

        $expected = base64_encode(hash_hmac('sha1', $data, $authToken, true));

        if (!hash_equals($expected, $twilioSignature)) {
            Log::warning('[TitanHello] Invalid Twilio signature', [
                'expected' => $expected,
                'provided' => $twilioSignature,
                'url' => $url,
            ]);
            return response('Invalid Twilio signature', 403);
        }

        return $next($request);
    }
}
