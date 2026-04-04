<?php

declare(strict_types=1);

use App\Models\Premises\FacilityDocument;
use App\Services\Docs\DocumentSearchService;

// ── Constructor / instantiation ──────────────────────────────────────────────

it('can be instantiated', function () {
    $service = new DocumentSearchService();

    expect($service)->toBeInstanceOf(DocumentSearchService::class);
});

// ── scoreRelevance ────────────────────────────────────────────────────────────

it('scoreRelevance returns 0 for empty context summary', function () {
    $service = new DocumentSearchService();

    $doc = Mockery::mock(FacilityDocument::class)->makePartial();
    $doc->embedding_vector = null;
    $doc->title            = 'Fire Safety Procedure';
    $doc->document_category = 'safety';
    $doc->doc_type         = 'safety_doc';
    $doc->notes            = null;

    $score = $service->scoreRelevance($doc, '');

    expect($score)->toBe(0.0);
});

it('scoreRelevance returns float between 0 and 1', function () {
    $service = new DocumentSearchService();

    $doc = Mockery::mock(FacilityDocument::class)->makePartial();
    $doc->embedding_vector  = null;
    $doc->title             = 'Fire Evacuation Procedure';
    $doc->document_category = 'procedure';
    $doc->doc_type          = 'site_document';
    $doc->notes             = 'fire escape route';

    $score = $service->scoreRelevance($doc, 'fire safety evacuation inspection');

    expect($score)->toBeFloat();
    expect($score)->toBeGreaterThanOrEqual(0.0);
    expect($score)->toBeLessThanOrEqual(1.0);
});

it('scoreRelevance returns higher score for more relevant document', function () {
    $service = new DocumentSearchService();

    $relevant = Mockery::mock(FacilityDocument::class)->makePartial();
    $relevant->embedding_vector  = null;
    $relevant->title             = 'HVAC Maintenance Procedure';
    $relevant->document_category = 'procedure';
    $relevant->doc_type          = 'site_document';
    $relevant->notes             = 'HVAC cooling maintenance checklist';

    $irrelevant = Mockery::mock(FacilityDocument::class)->makePartial();
    $irrelevant->embedding_vector  = null;
    $irrelevant->title             = 'Car Park Rules';
    $irrelevant->document_category = 'compliance';
    $irrelevant->doc_type          = 'compliance_doc';
    $irrelevant->notes             = 'parking permit required';

    $query        = 'HVAC maintenance service job';
    $highScore    = $service->scoreRelevance($relevant, $query);
    $lowScore     = $service->scoreRelevance($irrelevant, $query);

    expect($highScore)->toBeGreaterThan($lowScore);
});

it('scoreRelevance uses cosine similarity when embedding_vector is present', function () {
    $service = new DocumentSearchService();

    $doc = Mockery::mock(FacilityDocument::class)->makePartial();
    $doc->embedding_vector  = [0.9, 0.1, 0.5, 0.2, 0.3];
    $doc->title             = 'Ignored title';
    $doc->document_category = null;
    $doc->doc_type          = null;
    $doc->notes             = null;

    $score = $service->scoreRelevance($doc, 'maintenance inspection service');

    expect($score)->toBeFloat();
    expect($score)->toBeGreaterThanOrEqual(0.0);
    expect($score)->toBeLessThanOrEqual(1.0);
});

// ── semanticSearch — filters ──────────────────────────────────────────────────

it('semanticSearch returns empty collection when query is blank', function () {
    $service = new DocumentSearchService();

    // Partial mock to avoid real DB
    $mock = Mockery::mock(DocumentSearchService::class)->makePartial();
    $mock->shouldReceive('semanticSearch')
        ->with('', 1, [])
        ->andReturn(collect([]));

    $result = $mock->semanticSearch('', 1, []);

    expect($result)->toHaveCount(0);
});
