<?php

namespace Modules\Workflow\Tests\Feature;

use Tests\TestCase;

class WorkflowRoutesTest extends TestCase
{
    /** @test */
    public function routes_are_registered()
    {
        $this->assertNotNull(route('workflow.index'));
        $this->assertNotNull(route('workflow.check'));
        $this->assertNotNull(route('workflow.settings'));
    }
}
