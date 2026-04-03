<?php

namespace Modules\Inspection\Tests\Feature;

use Tests\TestCase;

class PoliciesLoadTest extends TestCase
{
    /** @test */
    public function policies_are_registered()
    {
        $this->assertTrue(app()->providerIsLoaded(\Modules\Inspection\Providers\AuthServiceProvider::class));
    }
}
