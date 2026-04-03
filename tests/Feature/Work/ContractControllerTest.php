<?php

use App\Models\User;
use App\Models\Work\ServiceAgreement;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('GET /dashboard/work/contracts/renewal-queue returns 401 for unauthenticated request', function () {
    $response = $this->getJson('/dashboard/work/contracts/renewal-queue');

    expect($response->status())->toBeIn([401, 302]);
});

it('GET /dashboard/work/contracts/{agreement}/entitlements requires authentication', function () {
    $response = $this->getJson('/dashboard/work/contracts/1/entitlements');

    expect($response->status())->toBeIn([401, 302]);
});

it('GET /dashboard/work/contracts/{agreement}/sla-status requires authentication', function () {
    $response = $this->getJson('/dashboard/work/contracts/1/sla-status');

    expect($response->status())->toBeIn([401, 302]);
});

it('GET /dashboard/work/contracts/{agreement}/health requires authentication', function () {
    $response = $this->getJson('/dashboard/work/contracts/1/health');

    expect($response->status())->toBeIn([401, 302]);
});

it('POST /dashboard/work/contracts/{agreement}/renew requires authentication', function () {
    $response = $this->postJson('/dashboard/work/contracts/1/renew');

    expect($response->status())->toBeIn([401, 302]);
});

it('GET /dashboard/work/contracts/renewal-queue returns paginated results when authenticated', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->getJson('/dashboard/work/contracts/renewal-queue');

    expect($response->status())->toBe(200);
    expect($response->json())->toHaveKey('renewal_queue');
});

it('POST /dashboard/work/contracts/{agreement}/renew returns 404 for missing agreement', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/dashboard/work/contracts/99999/renew');

    expect($response->status())->toBe(404);
});

it('POST /dashboard/work/contracts/{agreement}/renew validates billing_cycle', function () {
    $user = User::factory()->create();

    $agreement = ServiceAgreement::create([
        'company_id'  => $user->company_id ?? 1,
        'title'       => 'Test Contract',
        'status'      => 'active',
        'frequency'   => 'monthly',
    ]);

    $response = $this->actingAs($user)
        ->postJson("/dashboard/work/contracts/{$agreement->id}/renew", [
            'billing_cycle' => 'invalid-cycle',
        ]);

    expect($response->status())->toBe(422);
});
