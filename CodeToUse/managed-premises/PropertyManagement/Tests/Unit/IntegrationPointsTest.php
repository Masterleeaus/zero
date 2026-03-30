<?php
namespace Modules\PropertyManagement\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\PropertyManagement\Support\IntegrationPoints;

class IntegrationPointsTest extends TestCase
{
    public function test_registry_returns_array(): void
    {
        $this->assertIsArray(IntegrationPoints::all());
    }
}
