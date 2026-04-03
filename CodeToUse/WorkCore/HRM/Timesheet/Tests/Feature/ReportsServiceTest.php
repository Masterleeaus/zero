<?php

namespace Modules\Timesheet\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Timesheet\Entities\Timesheet;
use Modules\Timesheet\Services\Reports\TimesheetReportService;
use Tests\TestCase;

class ReportsServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_summarizes_time_and_cost_for_range(): void
    {
        // This is a lightweight smoke test; your app's factories may differ.
        Timesheet::query()->create([
            'user_id' => 1,
            'date' => Carbon::now()->toDateString(),
            'hours' => 1,
            'minutes' => 30,
            'fsm_cost_total' => 100,
            'created_by' => 1,
        ]);

        $svc = app(TimesheetReportService::class);
        $from = Carbon::now()->startOfMonth();
        $to = Carbon::now()->endOfMonth();

        $sum = $svc->summaryForRange(null, null, $from, $to);

        $this->assertEquals(1, $sum['entries']);
        $this->assertEquals(90, $sum['minutes']);
        $this->assertEquals(1.5, (float) $sum['hours_decimal']);
        $this->assertEquals(100.0, (float) $sum['cost_total']);
    }
}
