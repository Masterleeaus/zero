<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Work\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_shift_index_scoped_to_company(): void
    {
        $user = User::factory()->create(['company_id' => 1]);
        $other = User::factory()->create(['company_id' => 2]);

        Shift::factory()->create(['company_id' => 1, 'user_id' => $user->id]);
        Shift::factory()->create(['company_id' => 2, 'user_id' => $other->id]);

        $resp = $this->actingAs($user)->get(route('dashboard.work.shifts.index'));
        $resp->assertStatus(200);
        $this->assertCount(1, $resp->viewData('shifts'));
    }
}
