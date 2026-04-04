<?php

use App\Events\Work\ContractEntitlementExhausted;
use App\Models\Work\ContractEntitlement;
use App\Models\Work\ServiceAgreement;
use App\Services\Work\ContractEntitlementService;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->service = new ContractEntitlementService();
});

// ── ContractEntitlement model logic ──────────────────────────────────────────

it('ContractEntitlement isVisitExhausted returns true when visits_used >= max_visits', function () {
    $entitlement = new ContractEntitlement([
        'is_unlimited' => false,
        'max_visits'   => 5,
        'visits_used'  => 5,
        'max_hours'    => null,
        'hours_used'   => 0,
    ]);

    expect($entitlement->isVisitExhausted())->toBeTrue();
});

it('ContractEntitlement isVisitExhausted returns false when under limit', function () {
    $entitlement = new ContractEntitlement([
        'is_unlimited' => false,
        'max_visits'   => 10,
        'visits_used'  => 3,
        'max_hours'    => null,
        'hours_used'   => 0,
    ]);

    expect($entitlement->isVisitExhausted())->toBeFalse();
});

it('ContractEntitlement isVisitExhausted returns false for unlimited', function () {
    $entitlement = new ContractEntitlement([
        'is_unlimited' => true,
        'max_visits'   => 5,
        'visits_used'  => 99,
    ]);

    expect($entitlement->isVisitExhausted())->toBeFalse();
});

it('ContractEntitlement isVisitExhausted returns false when max_visits is null', function () {
    $entitlement = new ContractEntitlement([
        'is_unlimited' => false,
        'max_visits'   => null,
        'visits_used'  => 99,
    ]);

    expect($entitlement->isVisitExhausted())->toBeFalse();
});

it('ContractEntitlement isHoursExhausted returns true when hours_used >= max_hours', function () {
    $entitlement = new ContractEntitlement([
        'is_unlimited' => false,
        'max_hours'    => 10.0,
        'hours_used'   => 10.5,
    ]);

    expect($entitlement->isHoursExhausted())->toBeTrue();
});

it('ContractEntitlement remainingVisits calculates correctly', function () {
    $entitlement = new ContractEntitlement([
        'is_unlimited' => false,
        'max_visits'   => 10,
        'visits_used'  => 3,
    ]);

    expect($entitlement->remainingVisits())->toBe(7);
});

it('ContractEntitlement remainingVisits returns null for unlimited', function () {
    $entitlement = new ContractEntitlement([
        'is_unlimited' => true,
        'max_visits'   => 10,
        'visits_used'  => 3,
    ]);

    expect($entitlement->remainingVisits())->toBeNull();
});

it('ContractEntitlement remainingVisits returns null when max_visits is null', function () {
    $entitlement = new ContractEntitlement([
        'is_unlimited' => false,
        'max_visits'   => null,
        'visits_used'  => 3,
    ]);

    expect($entitlement->remainingVisits())->toBeNull();
});

it('ContractEntitlement remainingVisits never returns negative', function () {
    $entitlement = new ContractEntitlement([
        'is_unlimited' => false,
        'max_visits'   => 2,
        'visits_used'  => 5,
    ]);

    expect($entitlement->remainingVisits())->toBe(0);
});

it('ContractEntitlement remainingHours calculates correctly', function () {
    $entitlement = new ContractEntitlement([
        'is_unlimited' => false,
        'max_hours'    => 20.0,
        'hours_used'   => 7.5,
    ]);

    expect($entitlement->remainingHours())->toBe(12.5);
});

it('ContractEntitlement isDueForReset returns true when resets_on is in the past', function () {
    $entitlement = new ContractEntitlement([
        'period_type' => 'monthly',
        'resets_on'   => now()->subDay()->toDateString(),
    ]);

    expect($entitlement->isDueForReset())->toBeTrue();
});

it('ContractEntitlement isDueForReset returns false for contract period type', function () {
    $entitlement = new ContractEntitlement([
        'period_type' => 'contract',
        'resets_on'   => now()->subDay()->toDateString(),
    ]);

    expect($entitlement->isDueForReset())->toBeFalse();
});

it('ContractEntitlement isDueForReset returns false when resets_on is in the future', function () {
    $entitlement = new ContractEntitlement([
        'period_type' => 'monthly',
        'resets_on'   => now()->addMonth()->toDateString(),
    ]);

    expect($entitlement->isDueForReset())->toBeFalse();
});
