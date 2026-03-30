<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationUnreadTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_all_read_respects_company(): void
    {
        $user = User::factory()->create(['company_id' => 20]);
        $other = User::factory()->create(['company_id' => 21]);

        Notification::factory()->create([
            'notifiable_id'   => $user->id,
            'notifiable_type' => User::class,
            'company_id'      => 20,
        ]);

        Notification::factory()->create([
            'notifiable_id'   => $other->id,
            'notifiable_type' => User::class,
            'company_id'      => 21,
        ]);

        $this->actingAs($user)->post(route('dashboard.user.notifications.read'));

        $this->assertEquals(1, Notification::query()->whereNotNull('read_at')->count());
    }
}
