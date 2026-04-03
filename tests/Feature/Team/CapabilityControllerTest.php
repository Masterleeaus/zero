<?php

declare(strict_types=1);

use App\Http\Controllers\Team\CapabilityController;
use App\Models\User;
use App\Services\Team\CapabilityRegistryService;
use App\Services\Team\SkillComplianceService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->registry   = Mockery::mock(CapabilityRegistryService::class);
    $this->compliance = Mockery::mock(SkillComplianceService::class);

    $this->controller = new CapabilityController($this->registry, $this->compliance);
});

// ── Route registration ────────────────────────────────────────────────────────

it('registers capability routes under dashboard.team.capabilities', function () {
    $routes = collect(Route::getRoutes()->getRoutes());

    $names = $routes->map->getName()->filter()->values()->toArray();

    expect($names)->toContain('dashboard.team.capabilities.profile')
        ->toContain('dashboard.team.capabilities.skills')
        ->toContain('dashboard.team.capabilities.certifications')
        ->toContain('dashboard.team.capabilities.availability')
        ->toContain('dashboard.team.capabilities.gaps');
});

// ── skills endpoint ───────────────────────────────────────────────────────────

it('skills returns json with data key', function () {
    $user = Mockery::mock(User::class)->makePartial();

    $query = Mockery::mock();
    $query->shouldReceive('with')->with('skillDefinition')->andReturnSelf();
    $query->shouldReceive('get')->andReturn(collect([]));
    $user->shouldReceive('technicianSkills')->andReturn($query);

    $request = Mockery::mock(\Illuminate\Http\Request::class)->makePartial();
    $request->shouldReceive('user')->andReturn($user);

    $response = $this->controller->skills($request);

    expect($response->getStatusCode())->toBe(200);
    $json = json_decode($response->getContent(), true);
    expect($json)->toHaveKey('data');
});

// ── certifications endpoint ───────────────────────────────────────────────────

it('certifications returns json with data key', function () {
    $user = Mockery::mock(User::class)->makePartial();

    $query = Mockery::mock();
    $query->shouldReceive('orderByDesc')->with('issued_at')->andReturnSelf();
    $query->shouldReceive('get')->andReturn(collect([]));
    $user->shouldReceive('certifications')->andReturn($query);

    $request = Mockery::mock(\Illuminate\Http\Request::class)->makePartial();
    $request->shouldReceive('user')->andReturn($user);

    $response = $this->controller->certifications($request);

    expect($response->getStatusCode())->toBe(200);
    $json = json_decode($response->getContent(), true);
    expect($json)->toHaveKey('data');
});

// ── availability endpoint ─────────────────────────────────────────────────────

it('availability returns json with windows and overrides keys', function () {
    $user = Mockery::mock(User::class)->makePartial();

    $windowQuery = Mockery::mock();
    $windowQuery->shouldReceive('active')->andReturnSelf();
    $windowQuery->shouldReceive('get')->andReturn(collect([]));
    $user->shouldReceive('availabilityWindows')->andReturn($windowQuery);

    $overrideQuery = Mockery::mock();
    $overrideQuery->shouldReceive('orderBy')->with('date')->andReturnSelf();
    $overrideQuery->shouldReceive('get')->andReturn(collect([]));
    $user->shouldReceive('availabilityOverrides')->andReturn($overrideQuery);

    $request = Mockery::mock(\Illuminate\Http\Request::class)->makePartial();
    $request->shouldReceive('user')->andReturn($user);

    $response = $this->controller->availability($request);

    expect($response->getStatusCode())->toBe(200);
    $json = json_decode($response->getContent(), true);
    expect($json)->toHaveKeys(['windows', 'overrides']);
});

// ── gaps endpoint ─────────────────────────────────────────────────────────────

it('gaps returns 422 when company_id is not set', function () {
    $user = Mockery::mock(User::class)->makePartial();
    $user->company_id = null;

    $request = Mockery::mock(\Illuminate\Http\Request::class)->makePartial();
    $request->shouldReceive('user')->andReturn($user);

    $response = $this->controller->gaps($request);

    expect($response->getStatusCode())->toBe(422);
});

it('gaps delegates to SkillComplianceService and returns json', function () {
    $user = Mockery::mock(User::class)->makePartial();
    $user->company_id = 5;

    $this->compliance->shouldReceive('generateComplianceReport')
        ->once()
        ->with(5)
        ->andReturn([
            'company_id'   => 5,
            'generated_at' => '2026-04-03T00:00:00+00:00',
            'total_gaps'   => 0,
            'gaps_by_type' => [],
            'gaps'         => [],
        ]);

    $request = Mockery::mock(\Illuminate\Http\Request::class)->makePartial();
    $request->shouldReceive('user')->andReturn($user);

    $response = $this->controller->gaps($request);

    expect($response->getStatusCode())->toBe(200);
    $json = json_decode($response->getContent(), true);
    expect($json['company_id'])->toBe(5);
    expect($json['total_gaps'])->toBe(0);
});
