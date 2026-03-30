<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_are_company_scoped(): void
    {
        $user = User::factory()->create(['company_id' => 10]);
        $other = User::factory()->create(['company_id' => 20]);

        Notification::factory()->create([
            'company_id' => 10,
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
            'data' => ['message' => 'Hello'],
        ]);

        Notification::factory()->create([
            'company_id' => 20,
            'notifiable_id' => $other->id,
            'notifiable_type' => User::class,
            'data' => ['message' => 'Other'],
        ]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard.notifications.index'));

        $response->assertOk();
        $this->assertSame(1, $response->viewData('notifications')->count());
    }
}
