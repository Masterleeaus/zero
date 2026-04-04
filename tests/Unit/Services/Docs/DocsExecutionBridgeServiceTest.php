<?php

declare(strict_types=1);

use App\Events\Docs\DocumentsInjectedForJob;
use App\Events\Docs\MandatoryDocumentAcknowledged;
use App\Models\Inspection\InspectionInstance;
use App\Models\Premises\DocumentInjectionRule;
use App\Models\Premises\FacilityDocument;
use App\Models\Premises\InspectionInjectedDocument;
use App\Models\Premises\JobInjectedDocument;
use App\Models\User;
use App\Models\Work\ServiceJob;
use App\Services\Docs\DocsExecutionBridgeService;
use App\Services\Docs\DocumentSearchService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;

// ── Constructor / instantiation ──────────────────────────────────────────────

it('can be instantiated with DocumentSearchService', function () {
    $search  = Mockery::mock(DocumentSearchService::class);
    $service = new DocsExecutionBridgeService($search);

    expect($service)->toBeInstanceOf(DocsExecutionBridgeService::class);
});

// ── applyRuleBasedInjection — company_id guard ────────────────────────────────

it('returns empty collection when context has no company_id', function () {
    $search  = Mockery::mock(DocumentSearchService::class);
    $service = new DocsExecutionBridgeService($search);

    $job = Mockery::mock(ServiceJob::class)->makePartial();
    $job->company_id = null;

    $result = $service->applyRuleBasedInjection($job);

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result)->toHaveCount(0);
});

// ── getMandatoryUnacknowledged — ServiceJob context ───────────────────────────

it('getMandatoryUnacknowledged returns collection for ServiceJob', function () {
    $search  = Mockery::mock(DocumentSearchService::class);
    $service = new DocsExecutionBridgeService($search);

    $pivotQuery = Mockery::mock();
    $pivotQuery->shouldReceive('where')->andReturnSelf();
    $pivotQuery->shouldReceive('whereNull')->andReturnSelf();
    $pivotQuery->shouldReceive('with')->andReturnSelf();
    $pivotQuery->shouldReceive('get')->andReturn(collect([]));

    $job = Mockery::mock(ServiceJob::class)->makePartial();
    $job->id = 1;

    // Test that the method runs and returns a Collection
    // (full DB test covered in feature tests)
    expect($service->getMandatoryUnacknowledged($job))->toBeInstanceOf(Collection::class);
});

// ── getMandatoryUnacknowledged — InspectionInstance context ──────────────────

it('getMandatoryUnacknowledged returns collection for InspectionInstance', function () {
    $search  = Mockery::mock(DocumentSearchService::class);
    $service = new DocsExecutionBridgeService($search);

    $inspection = Mockery::mock(InspectionInstance::class)->makePartial();
    $inspection->id = 1;

    expect($service->getMandatoryUnacknowledged($inspection))->toBeInstanceOf(Collection::class);
});

it('getMandatoryUnacknowledged returns empty collection for unknown context', function () {
    $search  = Mockery::mock(DocumentSearchService::class);
    $service = new DocsExecutionBridgeService($search);

    $unknownModel = new class extends \Illuminate\Database\Eloquent\Model {};

    expect($service->getMandatoryUnacknowledged($unknownModel))->toHaveCount(0);
});

// ── applyAIRelevanceScoring ───────────────────────────────────────────────────

it('applyAIRelevanceScoring returns empty collection when candidates are empty', function () {
    $search  = Mockery::mock(DocumentSearchService::class);
    $service = new DocsExecutionBridgeService($search);

    $job = Mockery::mock(ServiceJob::class)->makePartial();
    $job->company_id = 1;

    $result = $service->applyAIRelevanceScoring($job, collect());

    expect($result)->toHaveCount(0);
});

it('applyAIRelevanceScoring skips documents below relevance threshold', function () {
    $search = Mockery::mock(DocumentSearchService::class);
    $search->shouldReceive('scoreRelevance')->andReturn(0.1); // below 0.5 threshold

    $service = new DocsExecutionBridgeService($search);

    $job = Mockery::mock(ServiceJob::class)->makePartial();
    $job->company_id = 1;
    $job->id         = 99;

    $doc = Mockery::mock(FacilityDocument::class)->makePartial();
    $doc->id          = 1;
    $doc->is_mandatory = false;

    $result = $service->applyAIRelevanceScoring($job, collect([$doc]));

    expect($result)->toHaveCount(0);
});
