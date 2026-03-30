<?php

namespace Tests\Feature;

use App\Http\Controllers\Core\Insights\InsightsController;
use App\Models\Crm\Customer;
use App\Models\Crm\Enquiry;
use App\Models\Money\Invoice;
use App\Models\Money\Quote;
use App\Models\User;
use App\Models\UserSupport;
use App\Models\Work\Attendance;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServiceJob;
use App\Models\Work\Timelog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Tests\TestCase;

class InsightsMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_overview_provides_operational_metrics(): void
    {
        $user = User::factory()->create(['company_id' => 30]);
        $this->actingAs($user);

        Enquiry::factory()->create(['company_id' => 30]);
        Customer::factory()->create(['company_id' => 30]);
        ServiceJob::factory()->create(['company_id' => 30, 'scheduled_at' => now()->addDay()]);
        Timelog::factory()->create(['company_id' => 30, 'duration_minutes' => 30]);
        Attendance::factory()->create(['company_id' => 30, 'status' => 'open']);
        ServiceAgreement::factory()->create(['company_id' => 30, 'status' => 'active']);
        UserSupport::factory()->create(['company_id' => 30, 'status' => 'waiting_on_user']);
        Quote::factory()->create(['company_id' => 30]);
        Invoice::factory()->create(['company_id' => 30, 'status' => 'sent', 'balance' => 100]);

        $request = Request::create('/');
        $request->setUserResolver(fn () => $user);

        $controller = new InsightsController();
        /** @var View $view */
        $view = $controller->overview($request);
        $data = $view->getData();

        $this->assertArrayHasKey('attendanceOpen', $data);
        $this->assertEquals(1, $data['attendanceOpen']);
        $this->assertEquals(1, $data['agreementsActive']);
        $this->assertEquals(1, $data['supportWaitingUser']);
    }
}
