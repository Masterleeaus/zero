<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

class WorkCoreBootTest extends TestCase
{
    public function test_workcore_vertical_config_defaults_to_cleaning(): void
    {
        $this->assertSame('cleaning', config('workcore.vertical'));
    }

    public function test_workcore_label_resolves_sites_to_jobs(): void
    {
        $this->assertSame('Jobs', workcore_label('sites'));
    }

    public function test_workcore_label_normalizes_hyphenated_keys(): void
    {
        $this->assertSame('Job', workcore_label('service-job'));
    }

    public function test_workcore_label_falls_back_to_title_cased_key(): void
    {
        $this->assertSame('Unknown', workcore_label('unknown-key', 'Unknown'));
    }
}
