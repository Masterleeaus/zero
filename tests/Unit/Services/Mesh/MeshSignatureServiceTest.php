<?php

declare(strict_types=1);

use App\Models\Mesh\MeshNode;
use App\Services\Mesh\MeshSignatureService;

beforeEach(function () {
    $this->service = new MeshSignatureService();
});

// ── signPayload ──────────────────────────────────────────────────────────────

it('signPayload returns a non-empty base64 string', function () {
    $payload   = ['company_id' => 1, 'action' => 'handshake'];
    $companyId = 1;

    $signature = $this->service->signPayload($payload, $companyId);

    expect($signature)->toBeString()->not->toBeEmpty();
    expect(base64_decode($signature, true))->not->toBeFalse();
});

it('signPayload produces the same signature for the same payload', function () {
    $payload   = ['company_id' => 1, 'event' => 'test'];
    $companyId = 1;

    $sig1 = $this->service->signPayload($payload, $companyId);
    $sig2 = $this->service->signPayload($payload, $companyId);

    expect($sig1)->toBe($sig2);
});

// ── verifyPayload ────────────────────────────────────────────────────────────

it('verifyPayload returns true for a correctly signed payload', function () {
    $payload = ['node_id' => 'abc', 'company_id' => 5];

    // The node's public_key is used as the signing secret in the peer's signPayload.
    $publicKey = 'test-peer-secret-key';

    $node = new MeshNode();
    $node->public_key = $publicKey;

    // Manually sign the payload with the same key
    ksort($payload);
    $canonical = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $signature = base64_encode(hash_hmac('sha256', $canonical, $publicKey, true));

    expect($this->service->verifyPayload($payload, $signature, $node))->toBeTrue();
});

it('verifyPayload returns false for a tampered payload', function () {
    $publicKey = 'test-peer-secret-key';

    $node = new MeshNode();
    $node->public_key = $publicKey;

    $originalPayload  = ['node_id' => 'abc'];
    ksort($originalPayload);
    $canonical = json_encode($originalPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $signature = base64_encode(hash_hmac('sha256', $canonical, $publicKey, true));

    // Tamper with the payload
    $tamperedPayload = ['node_id' => 'xyz'];

    expect($this->service->verifyPayload($tamperedPayload, $signature, $node))->toBeFalse();
});

it('verifyPayload returns false for an incorrect signature', function () {
    $node = new MeshNode();
    $node->public_key = 'correct-key';

    $payload      = ['action' => 'offer'];
    $badSignature = base64_encode('this-is-not-correct');

    expect($this->service->verifyPayload($payload, $badSignature, $node))->toBeFalse();
});

// ── buildMeshEnvelope ────────────────────────────────────────────────────────

it('buildMeshEnvelope returns required envelope keys', function () {
    // Mock Auth so the service can resolve a company_id
    $payload  = ['company_id' => 1, 'data' => 'test'];
    $envelope = $this->service->buildMeshEnvelope($payload, 'handshake');

    expect($envelope)->toHaveKeys(['action', 'payload', 'signed_at', 'signature']);
    expect($envelope['action'])->toBe('handshake');
    expect($envelope['signature'])->toBeString()->not->toBeEmpty();
});

it('buildMeshEnvelope signature can be verified using app key', function () {
    $payload  = ['company_id' => 1, 'ref' => 'test-ref'];
    $envelope = $this->service->buildMeshEnvelope($payload, 'test');

    // The envelope signature is built with the app key (fallback)
    $appKey   = config('app.key');
    $node     = new MeshNode();
    $node->public_key = $appKey;

    expect($this->service->verifyPayload($envelope['payload'], $envelope['signature'], $node))->toBeTrue();
});
