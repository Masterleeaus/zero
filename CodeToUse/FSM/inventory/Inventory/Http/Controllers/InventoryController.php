<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class InventoryController extends BaseController
{
    public function index(Request $request)
    {
        return view('inventory::index', [
            'title' => 'Inventory',
        ]);
    }
}
