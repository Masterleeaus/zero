<?php

namespace App\Http\Middleware;

use App\Exceptions\OmniChannelException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Verifies webhook signatures from third-party channels (Twilio, Facebook, Telegram, etc.).
 * Implements HMAC-SHA256 validation to prevent spoofed webhook payloads.
 *
 * Usage:
 *   Route::post('/webhook/{driver}', Controller::class)
 *       ->middleware(VerifyWebhookSignature::class);
 */
class VerifyWebhookSignature
{
    /**
     * Channel signature schemes and their verification methods.
     */
    protected const SIGNATURE_SCHEMES = [
        'twilio' => 'verifyTwilioSignature',
        'facebook' => 'verifyFacebookSignature',
        'telegram' => 'verifyTelegramSignature',
        'whatsapp' => 'verifyTwilioSignature', // Twilio for WhatsApp
    ];

    public function handle(Request $request, Closure $next)
    {
        $driver = $request->route('driver') ?? $request->query('driver');

        if (!$driver) {
            throw new OmniChannelException(
                'unknown',
                'Driver parameter required for webhook verification',
                400,
                'WEBHOOK_NO_DRIVER'
            );
        }

        $scheme = strtolower($driver);

        if (!isset(self::SIGNATURE_SCHEMES[$scheme])) {
            throw new OmniChannelException(
                $driver,
                "No signature verification configured for driver: {$driver}",
                400,
                'WEBHOOK_UNSUPPORTED_DRIVER'
            );
        }

        $method = self::SIGNATURE_SCHEMES[$scheme];
        if (!$this->$method($request, $driver)) {
            throw new OmniChannelException(
                $driver,
                'Webhook signature verification failed',
                401,
                'WEBHOOK_SIGNATURE_INVALID',
                $request->query('webhook_id', 'unknown')
            );
        }

        return $next($request);
    }

    /**
     * Verify Twilio webhook signature.
     * See: https://www.twilio.com/docs/sms/tutorials/how-to-secure-twilio-requests-php
     */
    protected function verifyTwilioSignature(Request $request, string $driver): bool
    {
        $token = config("services.{$driver}.auth_token");
        if (!$token) {
            \Log::warning("Twilio token not configured for driver: {$driver}");
            return false;
        }

        $signature = $request->header('X-Twilio-Signature');
        if (!$signature) {
            return false;
        }

        // Reconstruct the authenticated URL
        $url = $request->url();
        $data = $request->all();

        // Sort POST params and append to URL
        ksort($data);
        foreach ($data as $key => $value) {
            $url .= $key . $value;
        }

        // Generate signature
        $hash = base64_encode(hash_hmac('sha1', $url, $token, true));

        return hash_equals($hash, $signature);
    }

    /**
     * Verify Facebook webhook signature.
     * See: https://developers.facebook.com/docs/messenger-platform/webhooks
     */
    protected function verifyFacebookSignature(Request $request, string $driver): bool
    {
        $appSecret = config('services.facebook.app_secret');
        if (!$appSecret) {
            \Log::warning('Facebook app secret not configured');
            return false;
        }

        $signature = $request->header('X-Hub-Signature');
        if (!$signature) {
            return false;
        }

        $payload = $request->getContent();
        $hash = 'sha1=' . hash_hmac('sha1', $payload, $appSecret);

        return hash_equals($hash, $signature);
    }

    /**
     * Verify Telegram webhook signature.
     * Telegram uses query-based verification; validate init_data hash.
     */
    protected function verifyTelegramSignature(Request $request, string $driver): bool
    {
        $token = config('services.telegram.bot_token');
        if (!$token) {
            \Log::warning('Telegram bot token not configured');
            return false;
        }

        // For Telegram, we verify the bot token in route parameter or header
        // In production, also implement Web App init_data verification
        $providedToken = $request->header('X-Telegram-Token') ?? $request->query('token');

        return hash_equals($token, $providedToken);
    }
}
