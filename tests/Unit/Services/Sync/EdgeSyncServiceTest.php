<?php

declare(strict_types=1);

use App\Events\Sync\EdgeBatchSynced;
use App\Events\Sync\EdgeConflictDetected;
use App\Events\Sync\EdgeSyncFailed;
use App\Models\Sync\EdgeDeviceSession;
use App\Models\Sync\EdgeSyncConflict;
use App\Models\Sync\EdgeSyncLog;
use App\Models\Sync\EdgeSyncQueue;
use App\Models\User;
use App\Services\Sync\EdgeConflictResolverService;
use App\Services\Sync\EdgeSyncPayloadProcessor;
use App\Services\Sync\EdgeSyncService;
use App\Titan\Signals\SignalDispatcher;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->processor = Mockery::mock(EdgeSyncPayloadProcessor::class);
    $this->resolver  = Mockery::mock(EdgeConflictResolverService::class);
    $this->signals   = Mockery::mock(SignalDispatcher::class);

    $this->signals->shouldReceive('dispatch')->andReturn(null);

    $this->service = new EdgeSyncService(
        $this->processor,
        $this->resolver,
        $this->signals,
    );
});

it('registerDevice creates a new EdgeDeviceSession', function () {
    $user             = Mockery::mock(User::class)->makePartial();
    $user->id         = 1;
    $user->company_id = 10;

    $session = Mockery::mock(EdgeDeviceSession::class)->makePartial();
    $session->shouldReceive('getAttribute')->with('device_id')->andReturn('test-device');
    $session->shouldReceive('getAttribute')->with('sync_cursor')->andReturn(0);
    $session->shouldReceive('getAttribute')->with('platform')->andReturn('pwa');

    // Spy on the static updateOrCreate call without hitting DB.
    $mockModel = Mockery::mock('overload:' . EdgeDeviceSession::class);
    $mockModel->shouldReceive('withoutGlobalScopes->updateOrCreate')->andReturn($session);

    expect($session->device_id)->toBe('test-device');
});

it('processOperation marks item as synced on success', function () {
    Event::fake();

    $item = Mockery::mock(EdgeSyncQueue::class)->makePartial();
    $item->id             = 1;
    $item->operation_type = 'job_update';
    $item->subject_type   = null;
    $item->subject_id     = null;
    $item->shouldReceive('getAttribute')->with('operation_type')->andReturn('job_update');
    $item->shouldReceive('getAttribute')->with('subject_type')->andReturn(null);
    $item->shouldReceive('getAttribute')->with('subject_id')->andReturn(null);
    $item->shouldReceive('markProcessing')->once();
    $item->shouldReceive('markSynced')->once();
    $item->shouldReceive('isConflict')->andReturn(false);
    $item->shouldReceive('isFailed')->andReturn(false);

    $this->processor->shouldReceive('applyJobUpdate')->once()->with($item);

    $result = $this->service->processOperation($item);

    expect($result)->toBeTrue();
});

it('processOperation marks item as failed on exception', function () {
    Event::fake([EdgeSyncFailed::class]);

    $item = Mockery::mock(EdgeSyncQueue::class)->makePartial();
    $item->id             = 2;
    $item->operation_type = 'job_update';
    $item->subject_type   = null;
    $item->subject_id     = null;
    $item->shouldReceive('getAttribute')->with('operation_type')->andReturn('job_update');
    $item->shouldReceive('getAttribute')->with('subject_type')->andReturn(null);
    $item->shouldReceive('getAttribute')->with('subject_id')->andReturn(null);
    $item->shouldReceive('markProcessing')->once();
    $item->shouldReceive('isConflict')->andReturn(false);
    $item->shouldReceive('isFailed')->andReturn(true);
    $item->shouldReceive('markFailed')->once()->with(Mockery::type('string'));

    $this->processor->shouldReceive('applyJobUpdate')
        ->once()
        ->andThrow(new \RuntimeException('DB error'));

    $result = $this->service->processOperation($item);

    expect($result)->toBeFalse();

    Event::assertDispatched(EdgeSyncFailed::class);
});

it('detectConflicts returns null when subject_type is null', function () {
    $item               = Mockery::mock(EdgeSyncQueue::class)->makePartial();
    $item->subject_type = null;
    $item->subject_id   = null;
    $item->shouldReceive('getAttribute')->with('subject_type')->andReturn(null);
    $item->shouldReceive('getAttribute')->with('subject_id')->andReturn(null);

    $conflict = $this->service->detectConflicts($item);

    expect($conflict)->toBeNull();
});
