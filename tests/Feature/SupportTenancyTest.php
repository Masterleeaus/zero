<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserSupport;
use App\Models\UserSupportMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportTenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_messages_scope_to_company(): void
    {
        $userA = User::factory()->create(['company_id' => 1]);
        $userB = User::factory()->create(['company_id' => 2]);

        $ticketA = UserSupport::factory()->create(['user_id' => $userA->id, 'company_id' => 1]);
        UserSupportMessage::factory()->create(['user_support_id' => $ticketA->id, 'company_id' => 1]);

        $ticketB = UserSupport::factory()->create(['user_id' => $userB->id, 'company_id' => 2]);
        UserSupportMessage::factory()->create(['user_support_id' => $ticketB->id, 'company_id' => 2]);

        $this->actingAs($userA);

        $this->assertCount(1, UserSupport::all());
        $this->assertCount(1, $ticketA->messages);
    }
}
