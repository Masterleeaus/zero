<?php

namespace Tests\Feature;

use App\Models\UserSupport;
use App\Services\Support\SupportLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_lifecycle_transitions_and_timestamps(): void
    {
        $ticket = UserSupport::factory()->create([
            'status'      => 'open',
            'resolved_at' => now()->subDay(),
        ]);

        $service = new SupportLifecycleService();

        $service->processReplies($ticket, 'agent');
        $this->assertEquals('waiting_on_user', $ticket->fresh()->status);
        $this->assertNull($ticket->fresh()->resolved_at);

        $service->markStale($ticket->company_id, now()->addDay());
        $this->assertEquals('stale', $ticket->fresh()->status);

        $service->autoResolveInactive($ticket->company_id, now()->addDay());
        $this->assertEquals('resolved', $ticket->fresh()->status);
        $this->assertNotNull($ticket->fresh()->resolved_at);
    }
}
