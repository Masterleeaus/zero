<?php
namespace Modules\ManagedPremises\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\ManagedPremises\Support\IntegrationPoints;

class IntegrationPointsTest extends TestCase
{
    public function test_registry_returns_array(): void
    {
        $this->assertIsArray(IntegrationPoints::all());
    }
}
