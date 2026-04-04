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
    $request = Mockery::mock(Request::class)->makePartial();
    $request->shouldReceive('validate')->andReturn([
        'node_id'    => '00000000-0000-0000-0000-000000000001',
        'node_name'  => 'Peer',
        'node_url'   => 'https://peer.example.com',
        'public_key' => 'key',
    ]);
    $request->shouldReceive('header')->with('X-Mesh-Signature', '')->andReturn('sig');
    $request->shouldReceive('except')->with(['_token'])->andReturn([]);

    MeshNode::shouldReceive('withoutGlobalScope')
        ->with('company')
        ->andReturnSelf();
    MeshNode::shouldReceive('where')->andReturnSelf();
    MeshNode::shouldReceive('first')->andReturn(null);

    $response = $this->controller->handshake($request);

    expect($response->getStatusCode())->toBe(401);
    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveKey('error');
});

// ── handshake: bad signature returns 401 ─────────────────────────────────────

it('handshake returns 401 when signature verification fails', function () {
    $node = Mockery::mock(MeshNode::class)->makePartial();
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

    MeshNode::shouldReceive('withoutGlobalScope')->with('company')->andReturnSelf();
    MeshNode::shouldReceive('where')->andReturnSelf();
    MeshNode::shouldReceive('first')->andReturn($node);

    $this->signatures->shouldReceive('verifyPayload')
        ->andReturn(false);

    $response = $this->controller->handshake($request);

    expect($response->getStatusCode())->toBe(401);
});

// ── handshake: valid node and signature returns 200 ──────────────────────────

it('handshake returns 200 when node is found and signature is valid', function () {
    $node = Mockery::mock(MeshNode::class)->makePartial();
    $node->public_key  = 'test-key';
    $node->company_id  = 1;
    $node->trust_level = MeshNode::TRUST_STANDARD;
    $node->shouldReceive('meetsMinTrustLevel')->andReturn(true);

    $request = Mockery::mock(Request::class)->makePartial();
    $request->shouldReceive('validate')->andReturn([
        'node_id'    => '00000000-0000-0000-0000-000000000003',
        'node_name'  => 'Peer',
        'node_url'   => 'https://peer.example.com',
        'public_key' => 'test-key',
    ]);
    $request->shouldReceive('header')->with('X-Mesh-Signature', '')->andReturn('valid-sig');
    $request->shouldReceive('except')->with(['_token'])->andReturn([]);

    MeshNode::shouldReceive('withoutGlobalScope')->with('company')->andReturnSelf();
    MeshNode::shouldReceive('where')->andReturnSelf();
    MeshNode::shouldReceive('first')->andReturn($node);

    $this->signatures->shouldReceive('verifyPayload')
        ->andReturn(true);

    $this->registry->shouldReceive('performHandshake')->with($node)->andReturn(true);

    $response = $this->controller->handshake($request);

    expect($response->getStatusCode())->toBe(200);
    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveKey('status');
    expect($data['status'])->toBe('ok');
});
