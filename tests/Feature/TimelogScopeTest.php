<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Work\ServiceJob;
use App\Models\Work\Timelog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimelogScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_timelogs_are_company_scoped(): void
    {
        $user = User::factory()->create(['company_id' => 1]);
        $job = ServiceJob::factory()->create(['company_id' => 1]);
        Timelog::factory()->create(['company_id' => 1, 'user_id' => $user->id, 'service_job_id' => $job->id]);

        Timelog::factory()->create(['company_id' => 2]);

        $this->actingAs($user);
        $response = $this->get(route('dashboard.work.timelogs.index'));
        $response->assertOk();
        $this->assertSame(1, $response->viewData('timelogs')->total());
    }
}
