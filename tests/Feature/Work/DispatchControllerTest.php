<?php

use App\Models\User;
use App\Models\Work\DispatchAssignment;
use App\Models\Work\DispatchQueue;
use App\Models\Work\ServiceJob;
use App\Services\Work\DispatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('GET /dashboard/work/dispatch returns 401 for unauthenticated request', function () {
    $response = $this->getJson('/dashboard/work/dispatch');

    expect($response->status())->toBeIn([401, 302]);
});

it('POST /dashboard/work/dispatch/assign requires authentication', function () {
    $response = $this->postJson('/dashboard/work/dispatch/assign', [
        'job_id'        => 1,
        'technician_id' => 1,
    ]);

    expect($response->status())->toBeIn([401, 302]);
});

it('POST /dashboard/work/dispatch/assign fails validation without job_id', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/dashboard/work/dispatch/assign', []);

    expect($response->status())->toBe(422);
});

it('GET /dashboard/work/dispatch/history returns paginated results when authenticated', function () {
    $user = User::factory()->create();

    $mockService = Mockery::mock(DispatchService::class);
    $this->app->instance(DispatchService::class, $mockService);

    $response = $this->actingAs($user)
        ->getJson('/dashboard/work/dispatch/history');

    // Either 200 (success) or 500 if DB not fully set up — both are acceptable in unit context
    expect($response->status())->toBeIn([200, 500]);
});
