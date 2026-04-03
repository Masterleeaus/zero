<?php

use App\Models\Work\ContractSLABreach;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServiceJob;
use App\Services\Work\ContractSLAService;

beforeEach(function () {
    $this->service = new ContractSLAService();
});

// ── ContractSLABreach model logic ─────────────────────────────────────────────

it('ContractSLABreach overrunHours calculates correctly', function () {
    $breach = new ContractSLABreach([
        'sla_hours'    => 8,
        'actual_hours' => 12.5,
    ]);

    expect($breach->overrunHours())->toBe(4.5);
});

it('ContractSLABreach overrunHours returns 0 when not breached', function () {
    $breach = new ContractSLABreach([
        'sla_hours'    => 8,
        'actual_hours' => 5.0,
    ]);

    expect($breach->overrunHours())->toBe(0.0);
});

it('ContractSLABreach isNotified returns false when notified_at is null', function () {
    $breach = new ContractSLABreach([
        'notified_at' => null,
    ]);

    expect($breach->isNotified())->toBeFalse();
});

it('ContractSLABreach isNotified returns true when notified_at is set', function () {
    $breach = new ContractSLABreach([
        'notified_at' => now()->toDateTimeString(),
    ]);

    // Cast notified_at so model can read it
    $breach->setRawAttributes(['notified_at' => now()->toDateTimeString()]);
    $breach->syncOriginal();

    expect($breach->isNotified())->toBeTrue();
});

// ── ContractSLAService logic ──────────────────────────────────────────────────

it('checkSLAStatus returns defaults when job has no agreement', function () {
    $job = Mockery::mock(ServiceJob::class)->makePartial();
    $job->id = 1;
    $job->shouldReceive('getAttribute')->with('agreement_id')->andReturn(null);

    $status = $this->service->checkSLAStatus($job);

    expect($status['at_risk'])->toBeFalse()
        ->and($status['response_ok'])->toBeTrue()
        ->and($status['resolution_ok'])->toBeTrue()
        ->and($status['agreement_id'])->toBeNull();
});

it('checkSLAStatus returns no SLA hours when agreement is not found', function () {
    $job = Mockery::mock(ServiceJob::class)->makePartial();
    $job->id = 5;
    $job->agreement_id = 99999;
    $job->shouldReceive('getAttribute')->with('agreement_id')->andReturn(99999);

    // ServiceAgreement::find(99999) returns null in an isolated test without DB
    $status = $this->service->checkSLAStatus($job);

    expect($status['at_risk'])->toBeFalse()
        ->and($status['sla_response_hours'])->toBeNull()
        ->and($status['sla_resolution_hours'])->toBeNull();
});

it('ContractSLABreach overrunHours never returns negative', function () {
    $breach = new ContractSLABreach([
        'sla_hours'    => 10,
        'actual_hours' => 5.0,
    ]);

    expect($breach->overrunHours())->toBeGreaterThanOrEqual(0.0);
});
