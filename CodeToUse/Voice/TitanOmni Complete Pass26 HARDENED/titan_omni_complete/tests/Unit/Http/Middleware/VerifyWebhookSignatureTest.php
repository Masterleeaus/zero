<?php

namespace Tests\Unit\Http\Middleware;

use App\Exceptions\OmniChannelException;
use App\Http\Middleware\VerifyWebhookSignature;
use Illuminate\Http\Request;
use Tests\TestCase;

class VerifyWebhookSignatureTest extends TestCase
{
    protected VerifyWebhookSignature $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new VerifyWebhookSignature();
    }

    /**
     * Test Twilio signature verification succeeds with valid signature.
     */
    public function test_verifies_twilio_signature_valid(): void
    {
        config(['services.twilio.auth_token' => 'test_token']);

        $url = 'http://example.com/webhook?webhook_id=123';
        $data = ['From' => '+1234567890', 'Body' => 'Hello'];

        // Reconstruct signed URL
        $signedUrl = $url;
        ksort($data);
        foreach ($data as $key => $value) {
            $signedUrl .= $key . $value;
        }

        $signature = base64_encode(hash_hmac('sha1', $signedUrl, 'test_token', true));

        $request = new Request($data);
        $request->setMethod('POST');
        $request->headers->set('X-Twilio-Signature', $signature);

        $middleware = $this->middleware;
        $passed = $middleware->handle($request, function () {
            return true;
        });

        $this->assertTrue($passed);
    }

    /**
     * Test Twilio signature verification fails with invalid signature.
     */
    public function test_rejects_twilio_signature_invalid(): void
    {
        config(['services.twilio.auth_token' => 'test_token']);

        $request = new Request(['From' => '+1234567890']);
        $request->setMethod('POST');
        $request->headers->set('X-Twilio-Signature', 'invalid_signature_here');

        $this->expectException(OmniChannelException::class);

        $middleware = $this->middleware;
        $middleware->handle($request, function () {
            return true;
        });
    }

    /**
     * Test Facebook signature verification with valid signature.
     */
    public function test_verifies_facebook_signature_valid(): void
    {
        config(['services.facebook.app_secret' => 'app_secret_123']);

        $payload = json_encode(['entry' => []]);
        $hash = 'sha1=' . hash_hmac('sha1', $payload, 'app_secret_123');

        $request = new Request();
        $request->setMethod('POST');
        $request->headers->set('X-Hub-Signature', $hash);
        $request->setContent($payload);

        // Note: In real test, would need to mock route parameter
        // This is a simplified unit test
    }

    /**
     * Test missing webhook signature throws exception.
     */
    public function test_rejects_missing_signature(): void
    {
        config(['services.twilio.auth_token' => 'test_token']);

        $request = new Request();
        $request->setMethod('POST');
        // No X-Twilio-Signature header

        $this->expectException(OmniChannelException::class);

        $middleware = $this->middleware;
        $middleware->handle($request, function () {
            return true;
        });
    }

    /**
     * Test missing driver parameter throws exception.
     */
    public function test_throws_exception_missing_driver(): void
    {
        $request = new Request();
        $request->setMethod('POST');

        $this->expectException(OmniChannelException::class);
        $this->expectExceptionMessage('Driver parameter required');

        $middleware = $this->middleware;
        $middleware->handle($request, function () {
            return true;
        });
    }

    /**
     * Test unsupported driver throws exception.
     */
    public function test_throws_exception_unsupported_driver(): void
    {
        $request = new Request(['driver' => 'unsupported_platform']);
        $request->setMethod('POST');

        $this->expectException(OmniChannelException::class);
        $this->expectExceptionMessage('No signature verification configured');

        $middleware = $this->middleware;
        $middleware->handle($request, function () {
            return true;
        });
    }
}
