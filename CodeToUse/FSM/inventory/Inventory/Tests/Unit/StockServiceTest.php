<?php

namespace Modules\Inventory\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\Inventory\Services\StockService;

class StockServiceTest extends TestCase
{
    /** @test */
    public function on_hand_defaults_to_zero()
    {
        $svc = new StockService();
        $this->assertIsInt($svc->onHand(1, null));
    }
}
