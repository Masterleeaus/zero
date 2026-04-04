<?php

declare(strict_types=1);

use App\Models\Facility\AssetServiceEvent;
use App\Models\Facility\SiteAsset;
use App\Services\Predict\PredictionSignalExtractorService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->extractor = new PredictionSignalExtractorService();
});

// ── extractAssetSignals ───────────────────────────────────────────────────────

it('extractAssetSignals returns array of signal descriptors', function () {
    $asset = Mockery::mock(SiteAsset::class)->makePartial();
    $asset->install_date             = Carbon::now()->subYears(8);
    $asset->condition_status         = 'fair';
    $asset->last_serviced_at         = Carbon::now()->subDays(120);
    $asset->maintenance_interval_days = 90;
    $asset->company_id               = 1;

    $asset->shouldReceive('isMaintenanceDue')->andReturn(true);
    $asset->shouldReceive('isInspectionDue')->andReturn(false);
    $asset->shouldReceive('isUnderWarranty')->andReturn(false);

    $eventsQuery = Mockery::mock();
    $eventsQuery->shouldReceive('where')->with('event_type', 'repair')->andReturnSelf();
    $eventsQuery->shouldReceive('where')->andReturnSelf();
    $eventsQuery->shouldReceive('count')->andReturn(3);

    $asset->shouldReceive('serviceEvents')->andReturn($eventsQuery);

    $signals = $this->extractor->extractAssetSignals($asset);

    expect($signals)->toBeArray();
    expect(count($signals))->toBeGreaterThan(0);

    $types = array_column($signals, 'type');
    expect($types)->toContain('condition_status');
    expect($types)->toContain('maintenance_overdue');
    expect($types)->toContain('under_warranty');
});

it('extractAssetSignals weight is within valid range', function () {
    $asset = Mockery::mock(SiteAsset::class)->makePartial();
    $asset->install_date             = Carbon::now()->subYears(12);
    $asset->condition_status         = 'critical';
    $asset->last_serviced_at         = Carbon::now()->subDays(200);
    $asset->maintenance_interval_days = 90;

    $asset->shouldReceive('isMaintenanceDue')->andReturn(true);
    $asset->shouldReceive('isInspectionDue')->andReturn(true);
    $asset->shouldReceive('isUnderWarranty')->andReturn(false);

    $eventsQuery = Mockery::mock();
    $eventsQuery->shouldReceive('where')->with('event_type', 'repair')->andReturnSelf();
    $eventsQuery->shouldReceive('where')->andReturnSelf();
    $eventsQuery->shouldReceive('count')->andReturn(5);

    $asset->shouldReceive('serviceEvents')->andReturn($eventsQuery);

    $signals = $this->extractor->extractAssetSignals($asset);

    foreach ($signals as $signal) {
        expect($signal['weight'])->toBeGreaterThanOrEqual(0.0);
        expect($signal['weight'])->toBeLessThanOrEqual(1.0);
    }
});

it('extractAssetSignals skips age signal when install_date is null', function () {
    $asset = Mockery::mock(SiteAsset::class)->makePartial();
    $asset->install_date             = null;
    $asset->condition_status         = 'good';
    $asset->last_serviced_at         = null;
    $asset->maintenance_interval_days = 90;

    $asset->shouldReceive('isMaintenanceDue')->andReturn(false);
    $asset->shouldReceive('isInspectionDue')->andReturn(false);
    $asset->shouldReceive('isUnderWarranty')->andReturn(true);

    $eventsQuery = Mockery::mock();
    $eventsQuery->shouldReceive('where')->with('event_type', 'repair')->andReturnSelf();
    $eventsQuery->shouldReceive('where')->andReturnSelf();
    $eventsQuery->shouldReceive('count')->andReturn(0);

    $asset->shouldReceive('serviceEvents')->andReturn($eventsQuery);

    $signals = $this->extractor->extractAssetSignals($asset);

    $types = array_column($signals, 'type');
    expect($types)->not->toContain('asset_age_years');
});

// ── extractSLASignals ─────────────────────────────────────────────────────────

it('extractSLASignals returns expected signal types', function () {
    $agreement = Mockery::mock(\App\Models\Work\ServiceAgreement::class)->makePartial();
    $agreement->status = 'active';

    $jobQuery = Mockery::mock();
    $jobQuery->shouldReceive('whereNotIn')->andReturnSelf();
    $jobQuery->shouldReceive('where')->andReturnSelf();
    $jobQuery->shouldReceive('whereNotNull')->andReturnSelf();
    $jobQuery->shouldReceive('selectRaw')->andReturnSelf();
    $jobQuery->shouldReceive('value')->andReturn(4.5);
    $jobQuery->shouldReceive('count')->andReturn(2);

    $visitQuery = Mockery::mock();
    $visitQuery->shouldReceive('whereIn')->andReturnSelf();
    $visitQuery->shouldReceive('where')->andReturnSelf();
    $visitQuery->shouldReceive('count')->andReturn(1);

    $agreement->shouldReceive('jobs')->andReturn($jobQuery);
    $agreement->shouldReceive('visits')->andReturn($visitQuery);

    $signals = $this->extractor->extractSLASignals($agreement);

    expect($signals)->toBeArray();
    $types = array_column($signals, 'type');
    expect($types)->toContain('open_job_count');
    expect($types)->toContain('agreement_status');
});

// ── extractCapacitySignals ────────────────────────────────────────────────────

it('extractCapacitySignals returns signal array', function () {
    $signals = $this->extractor->extractCapacitySignals(1, Carbon::now()->addDay());

    expect($signals)->toBeArray();
    $types = array_column($signals, 'type');
    expect($types)->toContain('forecast_day_of_week');
});
