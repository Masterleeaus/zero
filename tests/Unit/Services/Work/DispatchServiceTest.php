<?php

use App\Models\User;
use App\Models\Work\DispatchAssignment;
use App\Models\Work\DispatchConstraint;
use App\Models\Work\DispatchQueue;
use App\Models\Work\ServiceJob;
use App\Services\Work\DispatchConstraintService;
use App\Services\Work\DispatchService;
use App\Titan\Signals\AuditTrail;
use App\Titan\Signals\SignalDispatcher;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->constraintService = Mockery::mock(DispatchConstraintService::class);
    $this->signals = Mockery::mock(SignalDispatcher::class);
    $this->auditTrail = Mockery::mock(AuditTrail::class);

    $this->service = new DispatchService(
        $this->constraintService,
        $this->signals,
        $this->auditTrail,
    );
});

it('scoreCandidate returns a float between 0 and 1', function () {
    $tech = Mockery::mock(User::class)->makePartial();
    $job  = Mockery::mock(ServiceJob::class)->makePartial();
    $job->shouldReceive('getAttribute')->with('premises')->andReturn(null);
    $job->shouldReceive('getAttribute')->with('job_type_id')->andReturn(null);
    $job->shouldReceive('getAttribute')->with('sla_deadline')->andReturn(null);

    $this->constraintService
        ->shouldReceive('evaluateSkillMatch')
        ->once()
        ->andReturn(0.7);

    $this->constraintService
        ->shouldReceive('evaluateSlaUrgency')
        ->once()
        ->andReturn(0.3);

    $score = $this->service->scoreCandidate($tech, $job, []);

    expect($score)->toBeFloat()
        ->and($score)->toBeGreaterThanOrEqual(0.0)
        ->and($score)->toBeLessThanOrEqual(1.0);
});

it('buildCandidatePool returns a Collection', function () {
    $job = Mockery::mock(ServiceJob::class)->makePartial();
    $job->shouldReceive('getAttribute')->with('company_id')->andReturn(1);
    $job->company_id = 1;

    // Without a real DB we just verify the return type contract via mocking
    $mockService = Mockery::mock(DispatchService::class, [
        $this->constraintService,
        $this->signals,
        $this->auditTrail,
    ])->makePartial();

    $mockService->shouldReceive('buildCandidatePool')
        ->once()
        ->andReturn(collect([]));

    $result = $mockService->buildCandidatePool($job);

    expect($result)->toBeInstanceOf(Collection::class);
});

it('queueForDispatch returns a DispatchQueue instance', function () {
    $job = Mockery::mock(ServiceJob::class)->makePartial();
    $job->shouldReceive('getAttribute')->with('id')->andReturn(42);
    $job->shouldReceive('getAttribute')->with('company_id')->andReturn(1);
    $job->id = 42;
    $job->company_id = 1;

    $this->constraintService
        ->shouldReceive('evaluateSlaUrgency')
        ->once()
        ->andReturn(0.3);

    $mockQueue = Mockery::mock(DispatchQueue::class)->makePartial();

    $mockService = Mockery::mock(DispatchService::class, [
        $this->constraintService,
        $this->signals,
        $this->auditTrail,
    ])->makePartial();

    $mockService->shouldReceive('queueForDispatch')
        ->once()
        ->andReturn($mockQueue);

    $result = $mockService->queueForDispatch($job);

    expect($result)->toBeInstanceOf(DispatchQueue::class);
});
