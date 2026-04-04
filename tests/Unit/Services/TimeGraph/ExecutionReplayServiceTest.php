<?php

declare(strict_types=1);

namespace Tests\Unit\Services\TimeGraph;

use App\Models\TimeGraph\ExecutionEvent;
use App\Models\Work\ServiceJob;
use App\Services\TimeGraph\ExecutionReplayService;
use App\Services\TimeGraph\ExecutionTimeGraphService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ExecutionReplayServiceTest extends TestCase
{
    use RefreshDatabase;

    private ExecutionTimeGraphService $graphService;
    private ExecutionReplayService $replayService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->graphService  = app(ExecutionTimeGraphService::class);
        $this->replayService = app(ExecutionReplayService::class);
    }

    public function test_build_replay_plan_returns_steps_up_to_time(): void
    {
        Event::fake();

        $job   = ServiceJob::factory()->create();
        $graph = $this->graphService->openGraph($job, 'Replay Test');

        $this->graphService->record($graph->graph_id, 'E1', $job, [], 'user_action');
        $this->graphService->record($graph->graph_id, 'E2', $job, [], 'system_trigger');

        $plan = $this->replayService->buildReplayPlan($graph, now()->addMinute());

        $this->assertArrayHasKey('graph_id', $plan);
        $this->assertArrayHasKey('steps', $plan);
        $this->assertCount(2, $plan['steps']);
    }

    public function test_describe_decision_returns_human_readable_string(): void
    {
        $event = new ExecutionEvent([
            'event_type'   => 'stage_transition',
            'actor_type'   => 'user',
            'actor_id'     => 5,
            'subject_type' => ServiceJob::class,
            'subject_id'   => 42,
        ]);

        $desc = $this->replayService->describeDecision($event);

        $this->assertStringContainsString('stage transition', $desc);
        $this->assertStringContainsString('User #5', $desc);
    }

    public function test_export_timeline_includes_graph_and_events(): void
    {
        Event::fake();

        $job   = ServiceJob::factory()->create();
        $graph = $this->graphService->openGraph($job, 'Export Test');
        $this->graphService->record($graph->graph_id, 'E1', $job, ['x' => 1], 'user_action');

        $exported = $this->replayService->exportTimeline($graph);

        $this->assertArrayHasKey('graph', $exported);
        $this->assertArrayHasKey('events', $exported);
        $this->assertCount(1, $exported['events']);
    }

    public function test_identify_anomalies_returns_empty_for_small_graphs(): void
    {
        Event::fake();

        $job   = ServiceJob::factory()->create();
        $graph = $this->graphService->openGraph($job, 'Anomaly Small Test');
        $this->graphService->record($graph->graph_id, 'E1', $job, [], 'user_action');

        $anomalies = $this->replayService->identifyAnomalies($graph);

        $this->assertEmpty($anomalies);
    }
}
