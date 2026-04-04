<?php

declare(strict_types=1);

use App\Http\Controllers\Predict\TitanPredictController;
use App\Models\Facility\SiteAsset;
use App\Models\Predict\Prediction;
use App\Models\Predict\PredictionOutcome;
use App\Models\User;
use App\Models\Work\ServiceAgreement;
use App\Services\Predict\TitanPredictService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->predictService = Mockery::mock(TitanPredictService::class);
    $this->controller     = new TitanPredictController($this->predictService);
});

// ── Route registration ────────────────────────────────────────────────────────

it('registers predict routes under dashboard.predict', function () {
    $routes = collect(Route::getRoutes()->getRoutes());
    $names  = $routes->map->getName()->filter()->values()->toArray();

    expect($names)->toContain('dashboard.predict.index')
        ->toContain('dashboard.predict.asset')
        ->toContain('dashboard.predict.agreement')
        ->toContain('dashboard.predict.capacity')
        ->toContain('dashboard.predict.dismiss')
        ->toContain('dashboard.predict.feedback');
});

// ── index endpoint ────────────────────────────────────────────────────────────

it('index returns 422 when company_id is not set', function () {
    $user             = Mockery::mock(User::class)->makePartial();
    $user->company_id = null;

    $request = Mockery::mock(Request::class)->makePartial();
    $request->shouldReceive('user')->andReturn($user);
    $request->shouldReceive('query')->with('type')->andReturn(null);

    $response = $this->controller->index($request);

    expect($response->getStatusCode())->toBe(422);
});

it('index returns json data when company is resolved', function () {
    $user             = Mockery::mock(User::class)->makePartial();
    $user->company_id = 5;

    $this->predictService
        ->shouldReceive('getActivePredictions')
        ->once()
        ->with(5, null)
        ->andReturn(collect([]));

    $request = Mockery::mock(Request::class)->makePartial();
    $request->shouldReceive('user')->andReturn($user);
    $request->shouldReceive('query')->with('type')->andReturn('');

    $response = $this->controller->index($request);

    expect($response->getStatusCode())->toBe(200);
    $json = json_decode($response->getContent(), true);
    expect($json)->toHaveKey('data');
});

// ── asset endpoint ─────────────────────────────────────────────────────────────

it('asset returns 404 when asset is not found', function () {
    $request = Mockery::mock(Request::class)->makePartial();

    // Mockery replaces the model query
    $response = $this->controller->asset($request, 99999);

    expect($response->getStatusCode())->toBe(404);
});

// ── capacity endpoint ─────────────────────────────────────────────────────────

it('capacity returns 422 when company_id is not set', function () {
    $user             = Mockery::mock(User::class)->makePartial();
    $user->company_id = null;

    $request = Mockery::mock(Request::class)->makePartial();
    $request->shouldReceive('user')->andReturn($user);
    $request->shouldReceive('query')->with('date')->andReturn(null);

    $response = $this->controller->capacity($request);

    expect($response->getStatusCode())->toBe(422);
});

it('capacity returns prediction json when company is resolved', function () {
    $user             = Mockery::mock(User::class)->makePartial();
    $user->company_id = 3;

    $prediction = Mockery::mock(Prediction::class)->makePartial();
    $prediction->shouldReceive('load')->with('signals')->andReturnSelf();

    $this->predictService
        ->shouldReceive('generateCapacityGapPrediction')
        ->once()
        ->andReturn($prediction);

    $request = Mockery::mock(Request::class)->makePartial();
    $request->shouldReceive('user')->andReturn($user);
    $request->shouldReceive('query')->with('date')->andReturn(null);

    $response = $this->controller->capacity($request);

    expect($response->getStatusCode())->toBe(200);
    $json = json_decode($response->getContent(), true);
    expect($json)->toHaveKey('data');
});

// ── dismiss endpoint ──────────────────────────────────────────────────────────

it('dismiss returns 404 when prediction not found', function () {
    $request = Mockery::mock(Request::class)->makePartial();
    $request->shouldReceive('user')->andReturn(new User());

    $response = $this->controller->dismiss($request, 99999);

    expect($response->getStatusCode())->toBe(404);
});

// ── feedback endpoint ─────────────────────────────────────────────────────────

it('feedback returns 404 when prediction not found', function () {
    $request = Mockery::mock(Request::class)->makePartial();

    $response = $this->controller->feedback($request, 99999);

    expect($response->getStatusCode())->toBe(404);
});
