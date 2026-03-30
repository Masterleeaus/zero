<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserSupport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_changes_on_replies(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $admin = User::factory()->create(['is_admin' => true]);

        $ticket = UserSupport::factory()->create([
            'company_id' => $user->company_id,
            'user_id'    => $user->id,
            'status'     => 'open',
        ]);

        $this->actingAs($admin)->post(route('dashboard.support.message', $ticket), ['message' => 'Hello']);
        $this->assertEquals('waiting_on_user', $ticket->fresh()->status);

        $this->actingAs($user)->post(route('dashboard.support.message', $ticket), ['message' => 'Reply']);
        $this->assertEquals('waiting_on_team', $ticket->fresh()->status);
    }
}
