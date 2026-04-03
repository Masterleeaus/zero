<?php

declare(strict_types=1);

use App\Events\Sync\EdgeConflictResolved;
use App\Models\Sync\EdgeSyncConflict;
use App\Models\Sync\EdgeSyncQueue;
use App\Services\Sync\EdgeConflictResolverService;
use App\Services\Sync\EdgeSyncPayloadProcessor;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->processor = Mockery::mock(EdgeSyncPayloadProcessor::class);
    $this->service   = new EdgeConflictResolverService($this->processor);
});

it('autoResolve returns false for manual strategy (job_complete)', function () {
    $item = Mockery::mock(EdgeSyncQueue::class)->makePartial();
    $item->shouldReceive('getAttribute')->with('operation_type')->andReturn('job_complete');
    $item->operation_type = 'job_complete';

    $conflict = Mockery::mock(EdgeSyncConflict::class)->makePartial();
    $conflict->shouldReceive('getAttribute')->with('sync_queue_id')->andReturn(1);
    $conflict->shouldReceive('syncQueue')->andReturn($item);
    $conflict->shouldReceive('getRelationValue')->with('syncQueue')->andReturn($item);
    $conflict->setRelation('syncQueue', $item);

    Log::shouldReceive('info')->once()->with('edge_sync.conflict_requires_manual_review', Mockery::any());

    $result = $this->service->autoResolve($conflict);

    expect($result)->toBeFalse();
});

it('autoResolve resolves checklist_response conflicts with merge strategy', function () {
    Event::fake([EdgeConflictResolved::class]);

    $item = Mockery::mock(EdgeSyncQueue::class)->makePartial();
    $item->operation_type = 'checklist_response';
    $item->shouldReceive('getAttribute')->with('operation_type')->andReturn('checklist_response');
    $item->shouldReceive('getAttribute')->with('payload')->andReturn([]);
    $item->shouldReceive('markSynced')->once();
    $item->shouldReceive('saveQuietly')->andReturn(true);

    $conflict = Mockery::mock(EdgeSyncConflict::class)->makePartial();
    $conflict->shouldReceive('getAttribute')->with('server_state')->andReturn([]);
    $conflict->shouldReceive('getAttribute')->with('client_state')->andReturn([]);
    $conflict->setRelation('syncQueue', $item);
    $conflict->shouldReceive('resolve')->once()->with('system', Mockery::any());

    Log::shouldReceive('info')->with('edge_sync.conflict_merge', Mockery::any());

    $this->processor->shouldReceive('applyChecklistResponse')->once();

    $result = $this->service->autoResolve($conflict);

    expect($result)->toBeTrue();

    Event::assertDispatched(EdgeConflictResolved::class);
});

it('applyResolution server_wins discards client payload and marks synced', function () {
    Event::fake([EdgeConflictResolved::class]);

    $item = Mockery::mock(EdgeSyncQueue::class)->makePartial();
    $item->operation_type = 'job_update';
    $item->shouldReceive('getAttribute')->with('operation_type')->andReturn('job_update');
    $item->shouldReceive('markSynced')->once();

    $conflict = Mockery::mock(EdgeSyncConflict::class)->makePartial();
    $conflict->setRelation('syncQueue', $item);
    $conflict->shouldReceive('resolve')->once()->with('system', Mockery::any());

    Log::shouldReceive('info')->with('edge_sync.conflict_server_wins', Mockery::any());

    $this->service->applyResolution($conflict, 'server_wins');

    // Processor should NOT be called for server_wins.
    $this->processor->shouldNotHaveReceived('applyJobUpdate');

    Event::assertDispatched(EdgeConflictResolved::class);
});

it('applyResolution client_wins applies the job update', function () {
    Event::fake([EdgeConflictResolved::class]);

    $item = Mockery::mock(EdgeSyncQueue::class)->makePartial();
    $item->operation_type = 'job_update';
    $item->shouldReceive('getAttribute')->with('operation_type')->andReturn('job_update');
    $item->shouldReceive('markSynced')->once();

    $conflict = Mockery::mock(EdgeSyncConflict::class)->makePartial();
    $conflict->setRelation('syncQueue', $item);
    $conflict->shouldReceive('resolve')->once()->with('system', Mockery::any());

    $this->processor->shouldReceive('applyJobUpdate')->once()->with($item);

    Log::shouldReceive('info')->with('edge_sync.conflict_client_wins', Mockery::any());

    $this->service->applyResolution($conflict, 'client_wins');

    Event::assertDispatched(EdgeConflictResolved::class);
});
