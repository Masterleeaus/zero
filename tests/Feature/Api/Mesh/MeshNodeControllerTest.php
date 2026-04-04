<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Mesh\MeshNodeController;
use App\Models\Mesh\MeshDispatchRequest;
use App\Models\Mesh\MeshNode;
use App\Services\Mesh\MeshDispatchService;
use App\Services\Mesh\MeshRegistryService;
use App\Services\Mesh\MeshSignatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->registry   = Mockery::mock(MeshRegistryService::class);
    $this->dispatch   = Mockery::mock(MeshDispatchService::class);
    $this->signatures = Mockery::mock(MeshSignatureService::class);

    $this->controller = new MeshNodeController(
        $this->registry,
        $this->dispatch,
        $this->signatures,
    );
});

// ── Route registration ────────────────────────────────────────────────────────

it('registers mesh api routes', function () {
    $routes = collect(Route::getRoutes()->getRoutes());
    $names  = $routes->map->getName()->filter()->values()->toArray();

    expect($names)->toContain('api.mesh.handshake')
        ->toContain('api.mesh.capabilities')
        ->toContain('api.mesh.dispatch.offer')
        ->toContain('api.mesh.dispatch.accept')
        ->toContain('api.mesh.dispatch.complete');
});

it('registers mesh dashboard routes', function () {
    $routes = collect(Route::getRoutes()->getRoutes());
    $names  = $routes->map->getName()->filter()->values()->toArray();

    expect($names)->toContain('dashboard.mesh.nodes')
        ->toContain('dashboard.mesh.requests')
        ->toContain('dashboard.mesh.settlements')
        ->toContain('dashboard.mesh.trust');
});

// ── handshake: unknown node returns 401 ──────────────────────────────────────

it('handshake returns 401 when node is not found', function () {
    $nodeId = '00000000-0000-0000-0000-000000000001';

    // Use Mockery alias to mock static Eloquent query
    $queryMock = Mockery::mock('overload:' . MeshNode::class);
    $queryMock->shouldReceive('withoutGlobalScope')->andReturnSelf();
    $queryMock->shouldReceive('where')->andReturnSelf();
    $queryMock->shouldReceive('first')->andReturn(null);

    $request = Mockery::mock(Request::class)->makePartial();
    $request->shouldReceive('validate')->andReturn([
        'node_id'    => $nodeId,
        'node_name'  => 'Peer',
        'node_url'   => 'https://peer.example.com',
        'public_key' => 'key',
    ]);
    $request->shouldReceive('header')->with('X-Mesh-Signature', '')->andReturn('sig');
    $request->shouldReceive('except')->with(['_token'])->andReturn([]);

    $response = $this->controller->handshake($request);

    expect($response->getStatusCode())->toBe(401);
    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveKey('error');
});

// ── MeshSignatureService::verifyPayload ───────────────────────────────────────

it('handshake returns 401 when signature verification fails', function () {
    $node             = new MeshNode();
    $node->public_key  = 'test-key';
    $node->company_id  = 1;
    $node->trust_level = MeshNode::TRUST_STANDARD;

    $request = Mockery::mock(Request::class)->makePartial();
    $request->shouldReceive('validate')->andReturn([
        'node_id'    => '00000000-0000-0000-0000-000000000002',
        'node_name'  => 'Peer',
        'node_url'   => 'https://peer.example.com',
        'public_key' => 'key',
    ]);
    $request->shouldReceive('header')->with('X-Mesh-Signature', '')->andReturn('bad-signature');
    $request->shouldReceive('except')->with(['_token'])->andReturn([]);

    $this->signatures->shouldReceive('verifyPayload')
        ->andReturn(false);

    // Controller does DB query via MeshNode — use a real node object via reflection
    // to bypass the Eloquent query; test the signature-check branch by passing
    // the node through the private method indirectly via mocking the query result.
    $nodeQuery = Mockery::mock('overload:' . MeshNode::class);
    $nodeQuery->shouldReceive('withoutGlobalScope')->andReturnSelf();
    $nodeQuery->shouldReceive('where')->andReturnSelf();
    $nodeQuery->shouldReceive('first')->andReturn($node);

    $response = $this->controller->handshake($request);

    expect($response->getStatusCode())->toBe(401);
});

// ── MeshSignatureService unit: verifyPayload ──────────────────────────────────

it('verifyPayload returns true for correctly signed payload', function () {
    $service   = new \App\Services\Mesh\MeshSignatureService();
    $publicKey = 'test-shared-secret';
    $payload   = ['node_id' => 'abc', 'company_id' => 5];

    $node             = new MeshNode();
    $node->public_key = $publicKey;

    // Produce the expected canonical signature
    ksort($payload);
    $canonical = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $signature = base64_encode(hash_hmac('sha256', $canonical, $publicKey, true));

    expect($service->verifyPayload($payload, $signature, $node))->toBeTrue();
});

it('verifyPayload returns false for tampered payload', function () {
    $service   = new \App\Services\Mesh\MeshSignatureService();
    $publicKey = 'test-shared-secret';

    $node             = new MeshNode();
    $node->public_key = $publicKey;

    $original  = ['node_id' => 'abc'];
    ksort($original);
    $signature = base64_encode(
        hash_hmac('sha256', json_encode($original, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $publicKey, true)
    );

    expect($service->verifyPayload(['node_id' => 'xyz'], $signature, $node))->toBeFalse();
});
