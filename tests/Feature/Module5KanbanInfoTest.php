<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Module 5 — fieldservice_kanban_info integration tests.
 *
 * Covers:
 *  - scheduleTimeRange accessor: time_only format
 *  - scheduleTimeRange accessor: date_and_time same-day format
 *  - scheduleTimeRange accessor: date_and_time cross-day format
 *  - scheduleTimeRange accessor: returns null when no scheduled_date_start
 *  - scheduleTimeRange accessor: end-time-only when no scheduled_date_end
 *  - config key 'schedule_time_range_format' defaults to 'time_only'
 */
class Module5KanbanInfoTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_time_range_returns_null_when_no_start(): void
    {
        $job = ServiceJob::factory()->create([
            'company_id'           => 70,
            'scheduled_date_start' => null,
            'scheduled_date_end'   => null,
        ]);

        $this->assertNull($job->schedule_time_range);
    }

    public function test_schedule_time_range_time_only_format(): void
    {
        config(['workcore.schedule_time_range_format' => 'time_only']);

        $job = ServiceJob::factory()->create([
            'company_id'           => 71,
            'scheduled_date_start' => '2025-04-01 09:00:00',
            'scheduled_date_end'   => '2025-04-01 11:30:00',
        ]);

        $this->assertSame('09:00 - 11:30', $job->schedule_time_range);
    }

    public function test_schedule_time_range_time_only_no_end(): void
    {
        config(['workcore.schedule_time_range_format' => 'time_only']);

        $job = ServiceJob::factory()->create([
            'company_id'           => 72,
            'scheduled_date_start' => '2025-04-01 14:00:00',
            'scheduled_date_end'   => null,
        ]);

        $this->assertSame('14:00', $job->schedule_time_range);
    }

    public function test_schedule_time_range_date_and_time_same_day(): void
    {
        config(['workcore.schedule_time_range_format' => 'date_and_time']);

        $job = ServiceJob::factory()->create([
            'company_id'           => 73,
            'scheduled_date_start' => '2025-04-01 09:00:00',
            'scheduled_date_end'   => '2025-04-01 11:30:00',
        ]);

        $this->assertSame('01/04/2025 09:00 - 11:30', $job->schedule_time_range);
    }

    public function test_schedule_time_range_date_and_time_cross_day(): void
    {
        config(['workcore.schedule_time_range_format' => 'date_and_time']);

        $job = ServiceJob::factory()->create([
            'company_id'           => 74,
            'scheduled_date_start' => '2025-04-01 22:00:00',
            'scheduled_date_end'   => '2025-04-02 06:00:00',
        ]);

        $this->assertSame('01/04/2025 22:00 - 02/04/2025 06:00', $job->schedule_time_range);
    }

    public function test_schedule_time_range_date_and_time_no_end(): void
    {
        config(['workcore.schedule_time_range_format' => 'date_and_time']);

        $job = ServiceJob::factory()->create([
            'company_id'           => 75,
            'scheduled_date_start' => '2025-04-01 10:00:00',
            'scheduled_date_end'   => null,
        ]);

        $this->assertSame('01/04/2025 10:00', $job->schedule_time_range);
    }

    public function test_schedule_time_range_format_config_defaults_to_time_only(): void
    {
        $this->assertSame('time_only', config('workcore.schedule_time_range_format'));
    }
}
