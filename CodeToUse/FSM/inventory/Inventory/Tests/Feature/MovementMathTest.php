<?php

namespace Modules\Inventory\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Inventory\Entities\{Item, StockMovement};
use Modules\Inventory\Services\StockService;

class MovementMathTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function on_hand_matches_sum_of_movements()
    {
        $item = Item::create(['name'=>'CalcDemo','qty'=>0]);
        // In 10
        StockMovement::create(['item_id'=>$item->id,'type'=>'in','qty_change'=>10]);
        // Out 4
        StockMovement::create(['item_id'=>$item->id,'type'=>'out','qty_change'=>-4]);
        // Adjust +3
        StockMovement::create(['item_id'=>$item->id,'type'=>'adjust','qty_change'=>3]);

        $svc = new StockService();
        $this->assertEquals(9, $svc->onHand($item->id)); // 10-4+3 = 9
    }
}
