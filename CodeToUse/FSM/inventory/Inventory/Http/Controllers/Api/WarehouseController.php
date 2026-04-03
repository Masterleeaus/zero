<?php

namespace Modules\Inventory\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Entities\Warehouse;

class WarehouseController extends Controller
{
    public function index() { return Warehouse::orderBy('name')->get(); }
    public function store(Request $r) { $w = Warehouse::create($r->validate(['name'=>'required|string|max:255','code'=>'nullable|string|max:64','location'=>'nullable|string|max:255'])); return response()->json($w,201); }
}
