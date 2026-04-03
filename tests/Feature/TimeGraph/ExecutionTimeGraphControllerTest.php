<?php

declare(strict_types=1);

namespace Tests\Feature\TimeGraph;

use App\Models\User;
use App\Models\Work\ServiceJob;
use App\Services\TimeGraph\ExecutionTimeGraphService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ExecutionTimeGraphControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_timeline_endpoint_returns_events(): void
    {
        Event::fake();

        $job     = ServiceJob::factory()->create();
        $service = app(ExecutionTimeGraphService::class);
        $graph   = $service->openGraph($job, 'Controller Test');
        $service->record($graph->graph_id, 'E1', $job, [], 'user_action');

        $user     = User::factory()->create();
        $response = $this->actingAs($user)->getJson(route('dashboard.timegraph.timeline', $graph->graph_id));

        $response->assertOk();
        $response->assertJsonStructure(['graph', 'events']);
    }

    public function test_graph_endpoint_returns_graph_with_relations(): void
    {
        Event::fake();

        $job     = ServiceJob::factory()->create();
        $service = app(ExecutionTimeGraphService::class);
        $graph   = $service->openGraph($job, 'Graph Endpoint Test');

        $user     = User::factory()->create();
        $response = $this->actingAs($user)->getJson(route('dashboard.timegraph.graph', $graph->graph_id));

        $response->assertOk();
        $response->assertJsonPath('graph_id', $graph->graph_id);
    }

    public function test_replay_endpoint_builds_plan(): void
    {
        Event::fake();

        $job     = ServiceJob::factory()->create();
        $service = app(ExecutionTimeGraphService::class);
        $graph   = $service->openGraph($job, 'Replay Endpoint Test');
        $service->record($graph->graph_id, 'E1', $job, [], 'user_action');

        $user     = User::factory()->create();
        $response = $this->actingAs($user)->postJson(
            route('dashboard.timegraph.replay', $graph->graph_id),
            ['to_time' => now()->addHour()->toIso8601String()]
        );

        $response->assertOk();
        $response->assertJsonStructure(['graph_id', 'steps']);
    }

    public function test_describe_endpoint_exports_timeline(): void
    {
        Event::fake();

        $job     = ServiceJob::factory()->create();
        $service = app(ExecutionTimeGraphService::class);
        $graph   = $service->openGraph($job, 'Describe Test');

        $user     = User::factory()->create();
        $response = $this->actingAs($user)->getJson(route('dashboard.timegraph.describe', $graph->graph_id));

        $response->assertOk();
        $response->assertJsonStructure(['graph', 'events']);
    }
}
