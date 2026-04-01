<?php

namespace Tests\Feature;

use App\Models\UserSupport;
use App\Models\User;
use App\Notifications\LiveNotification;
use App\Services\Support\SupportLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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

    public function test_assign_to_sets_status_and_notifies(): void
    {
        Notification::fake();

        $ticket = UserSupport::factory()->create();
        $assignee = User::factory()->create(['company_id' => $ticket->company_id]);

        $service = new SupportLifecycleService();

        $updatedTicket = $service->assignTo($ticket, $assignee->id);

        $this->assertEquals('waiting_on_team', $updatedTicket->status);
        $this->assertEquals($assignee->id, $updatedTicket->assigned_to);

        Notification::assertSentTo(
            $assignee,
            LiveNotification::class,
            fn (LiveNotification $notification) => str_contains(
                $notification->toArray($assignee)['data']['message'],
                'assigned to you'
            )
        );

        Notification::assertSentTo(
            $ticket->user,
            LiveNotification::class,
            fn (LiveNotification $notification) => str_contains(
                $notification->toArray($ticket->user)['data']['message'],
                'status changed to: waiting_on_team'
            )
        );
    }
}
