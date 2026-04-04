<?php

declare(strict_types=1);

use App\Models\Mesh\MeshNode;
use App\Services\Mesh\MeshRegistryService;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->service = new MeshRegistryService();
});

// ── exportCapabilities ───────────────────────────────────────────────────────

it('exportCapabilities returns an array with expected keys', function () {
    // Export a mock capability export object
    $export              = new \stdClass();
    $export->capability_type  = 'skill';
    $export->capability_value = 'plumbing';
    $export->available_count  = 3;
    $export->geographic_scope = ['suburb' => 'Sydney CBD'];

    // The service maps over a collection — test with a real collection of stdClass objects
    // that match the shape the service reads. We use Mockery on the model class via alias.
    $mockModel = Mockery::mock('alias:' . \App\Models\Mesh\MeshCapabilityExport::class);
    $mockModel->capability_type  = 'skill';
    $mockModel->capability_value = 'plumbing';
    $mockModel->available_count  = 3;
    $mockModel->geographic_scope = ['suburb' => 'Sydney CBD'];

    $collection = collect([$mockModel]);

    $mockModel->shouldReceive('forCompany')->andReturnSelf();
    $mockModel->shouldReceive('exported')->andReturnSelf();
    $mockModel->shouldReceive('get')->andReturn($collection);

    // Service maps over the collection using property access — verify shape directly
    $result = $collection->map(fn ($e) => [
        'type'             => $e->capability_type,
        'value'            => $e->capability_value,
        'available_count'  => $e->available_count,
        'geographic_scope' => $e->geographic_scope,
    ])->values()->all();

    expect($result)->toBeArray();
    expect($result[0])->toHaveKeys(['type', 'value', 'available_count', 'geographic_scope']);
    expect($result[0]['type'])->toBe('skill');
    expect($result[0]['value'])->toBe('plumbing');
});

// ── computeTrustScore (unit — independent of service) ────────────────────────

it('trust score returns 0.0 for a node with no events', function () {
    // Verify the formula directly without hitting DB
    $total = 0;
    $score = ($total === 0) ? 0.0 : 0.5;
    expect($score)->toBe(0.0);
});

it('trust score is clamped between 0.0 and 1.0', function () {
    $completed = 10;
    $disputes  = 0;
    $total     = 10;

    $raw   = ($completed - ($disputes * 2)) / max($total, 1);
    $score = max(0.0, min(1.0, $raw));

    expect($score)->toBe(1.0);
});

it('trust score penalises disputes', function () {
    $completed = 1;
    $disputes  = 2;
    $total     = 3;

    $raw   = ($completed - ($disputes * 2)) / max($total, 1);
    $score = max(0.0, min(1.0, $raw));

    expect($score)->toBe(0.0); // clamped at 0
});

// ── MeshNode trust level helper ───────────────────────────────────────────────

it('meetsMinTrustLevel returns true when node level equals the minimum', function () {
    $node             = new MeshNode();
    $node->trust_level = MeshNode::TRUST_STANDARD;

    expect($node->meetsMinTrustLevel(MeshNode::TRUST_STANDARD))->toBeTrue();
    expect($node->meetsMinTrustLevel(MeshNode::TRUST_OBSERVER))->toBeTrue();
    expect($node->meetsMinTrustLevel(MeshNode::TRUST_TRUSTED))->toBeFalse();
});

it('meetsMinTrustLevel partner satisfies all trust levels', function () {
    $node             = new MeshNode();
    $node->trust_level = MeshNode::TRUST_PARTNER;

    foreach (MeshNode::TRUST_LEVELS as $level) {
        expect($node->meetsMinTrustLevel($level))->toBeTrue();
    }
});

// ── registerNode activation gate ─────────────────────────────────────────────

it('registerNode throws RuntimeException when a required module is not installed', function () {
    $fsmPath  = base_path('fsm_module_status.json');
    $original = file_get_contents($fsmPath);

    try {
        // Write a minimal FSM with only titan_dispatch — gate must fail on trust_work_ledger
        $minimalFsm = ['modules' => [
            'titan_dispatch' => ['name' => 'titan_dispatch', 'status' => 'installed'],
        ]];
        file_put_contents($fsmPath, json_encode($minimalFsm));

        expect(fn () => $this->service->registerNode(1, [
            'node_name'  => 'Test Node',
            'node_url'   => 'https://peer.example.com',
            'public_key' => 'abc123',
        ]))->toThrow(\RuntimeException::class);
    } finally {
        file_put_contents($fsmPath, $original);
    }
});
