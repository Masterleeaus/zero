<?php

declare(strict_types=1);

use App\Models\Mesh\MeshCapabilityExport;
use App\Models\Mesh\MeshNode;
use App\Services\Mesh\MeshRegistryService;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->service = new MeshRegistryService();
});

// ── exportCapabilities ───────────────────────────────────────────────────────

it('exportCapabilities returns array of exported capabilities', function () {
    $companyId = 1;

    $export = Mockery::mock(MeshCapabilityExport::class)->makePartial();
    $export->capability_type  = 'skill';
    $export->capability_value = 'plumbing';
    $export->available_count  = 3;
    $export->geographic_scope = ['suburb' => 'Sydney CBD'];

    $collection = collect([$export]);

    // Stub the static query chain
    MeshCapabilityExport::shouldReceive('forCompany')
        ->with($companyId)
        ->andReturnSelf();
    MeshCapabilityExport::shouldReceive('exported')
        ->andReturnSelf();
    MeshCapabilityExport::shouldReceive('get')
        ->andReturn($collection);

    $result = $this->service->exportCapabilities($companyId);

    expect($result)->toBeArray();
    expect($result[0])->toHaveKeys(['type', 'value', 'available_count', 'geographic_scope']);
    expect($result[0]['type'])->toBe('skill');
    expect($result[0]['value'])->toBe('plumbing');
});

// ── getTrustedPeers ──────────────────────────────────────────────────────────

it('getTrustedPeers delegates to MeshNode query with correct trust level', function () {
    $companyId = 2;

    $nodeCollection = collect([]);

    MeshNode::shouldReceive('forCompany')
        ->with($companyId)
        ->andReturnSelf();
    MeshNode::shouldReceive('active')
        ->andReturnSelf();
    MeshNode::shouldReceive('withMinTrustLevel')
        ->with('standard')
        ->andReturnSelf();
    MeshNode::shouldReceive('get')
        ->andReturn($nodeCollection);

    $result = $this->service->getTrustedPeers($companyId);

    expect($result)->toBeInstanceOf(Collection::class);
});

it('getTrustedPeers uses standard as default min trust level', function () {
    $companyId = 3;

    MeshNode::shouldReceive('forCompany')->with($companyId)->andReturnSelf();
    MeshNode::shouldReceive('active')->andReturnSelf();
    MeshNode::shouldReceive('withMinTrustLevel')->with('standard')->andReturnSelf();
    MeshNode::shouldReceive('get')->andReturn(collect([]));

    $result = $this->service->getTrustedPeers($companyId);

    expect($result)->toBeInstanceOf(Collection::class);
});

// ── registerNode activation gate ─────────────────────────────────────────────

it('registerNode throws RuntimeException when activation gate fails', function () {
    // Write a temporary FSM file missing required modules
    $fsmPath  = base_path('fsm_module_status.json');
    $original = file_get_contents($fsmPath);

    try {
        // The current FSM has only titan_dispatch and capability_registry installed
        // — trust_work_ledger, titan_contracts, edge_sync, execution_time_graph are missing
        expect(fn () => $this->service->registerNode(1, [
            'node_name'  => 'Test Node',
            'node_url'   => 'https://peer.example.com',
            'public_key' => 'abc123',
        ]))->toThrow(\RuntimeException::class);
    } finally {
        // Always restore the original FSM file to avoid test side-effects
        file_put_contents($fsmPath, $original);
    }
});
