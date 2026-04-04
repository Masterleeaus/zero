<?php

declare(strict_types=1);

namespace Tests\Unit\Services\TimeGraph;

use App\Models\TimeGraph\ExecutionEvent;
use App\Models\TimeGraph\ExecutionGraph;
use App\Models\TimeGraph\ExecutionGraphCheckpoint;
use App\Models\Work\ServiceJob;
use App\Services\TimeGraph\ExecutionTimeGraphService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ExecutionTimeGraphServiceTest extends TestCase
{
    use RefreshDatabase;

    private ExecutionTimeGraphService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ExecutionTimeGraphService::class);
    }

    public function test_open_graph_creates_graph_record(): void
    {
        Event::fake();

        $job = ServiceJob::factory()->create();

        $graph = $this->service->openGraph($job, 'Test Graph');

        $this->assertInstanceOf(ExecutionGraph::class, $graph);
        $this->assertEquals('active', $graph->status);
        $this->assertEquals(get_class($job), $graph->root_subject_type);
        $this->assertEquals($job->getKey(), $graph->root_subject_id);
        $this->assertNotEmpty($graph->graph_id);
    }

    public function test_record_creates_event_with_correct_sequence(): void
    {
        Event::fake();

        $job   = ServiceJob::factory()->create();
        $graph = $this->service->openGraph($job, 'Sequence Test');

        $event1 = $this->service->record(
            graphId: $graph->graph_id,
            eventClass: 'TestEvent',
            subject: $job,
            payload: ['test' => 1],
            eventType: 'user_action',
            actorType: 'user',
            actorId: 1,
        );

        $event2 = $this->service->record(
            graphId: $graph->graph_id,
            eventClass: 'TestEvent2',
            subject: $job,
            payload: ['test' => 2],
            eventType: 'system_trigger',
            actorType: 'system',
        );

        $this->assertEquals(1, $event1->sequence);
        $this->assertEquals(2, $event2->sequence);
    }

    public function test_close_graph_marks_completed(): void
    {
        Event::fake();

        $job    = ServiceJob::factory()->create();
        $graph  = $this->service->openGraph($job, 'Close Test');
        $closed = $this->service->closeGraph($graph->graph_id);

        $this->assertEquals('completed', $closed->status);
        $this->assertNotNull($closed->completed_at);
    }

    public function test_get_timeline_returns_ordered_events(): void
    {
        Event::fake();

        $job   = ServiceJob::factory()->create();
        $graph = $this->service->openGraph($job, 'Timeline Test');

        $this->service->record($graph->graph_id, 'E1', $job, [], 'user_action');
        $this->service->record($graph->graph_id, 'E2', $job, [], 'system_trigger');

        $timeline = $this->service->getTimeline($graph->graph_id);

        $this->assertCount(2, $timeline);
        $this->assertEquals(1, $timeline->first()->sequence);
        $this->assertEquals(2, $timeline->last()->sequence);
    }

    public function test_create_checkpoint_stores_snapshot(): void
    {
        Event::fake();

        $job   = ServiceJob::factory()->create();
        $graph = $this->service->openGraph($job, 'Checkpoint Test');
        $this->service->record($graph->graph_id, 'E1', $job, [], 'user_action');

        $cp = $this->service->createCheckpoint($graph->graph_id, 'After step 1');

        $this->assertInstanceOf(ExecutionGraphCheckpoint::class, $cp);
        $this->assertEquals('After step 1', $cp->label);
        $this->assertNotNull($cp->state_snapshot);
    }

    public function test_find_causal_chain_traverses_parent_links(): void
    {
        Event::fake();

        $job   = ServiceJob::factory()->create();
        $graph = $this->service->openGraph($job, 'Causal Test');

        $e1 = $this->service->record($graph->graph_id, 'Root', $job, [], 'user_action');
        $e2 = $this->service->record($graph->graph_id, 'Child', $job, [], 'system_trigger', 'system', null, $e1->id);

        $chain = $this->service->findCausalChain($e2);

        $this->assertCount(2, $chain);
        $this->assertEquals($e1->id, $chain->first()->id);
        $this->assertEquals($e2->id, $chain->last()->id);
    }
}
