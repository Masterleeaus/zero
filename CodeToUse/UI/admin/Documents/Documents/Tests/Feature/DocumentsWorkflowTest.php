<?php

namespace Modules\Documents\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_placeholder(): void
    {
        $this->assertTrue(true);
    }
}
