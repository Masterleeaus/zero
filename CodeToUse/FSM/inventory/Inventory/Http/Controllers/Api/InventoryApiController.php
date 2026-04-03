<?php

namespace Modules\Inventory\Http\Controllers\Api;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InventoryApiController extends BaseController
{
    public function items(Request $request): JsonResponse
    {
        // Placeholder data; will be replaced if donor has models/migrations
        return response()->json(['data' => \Modules\Inventory\Entities\Item::query()->orderByDesc('id')->limit(100)->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        // Validate and persist; adjusted during donor mapping
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:128',
            'qty' => 'required|integer|min:0',
        ]);
         $item = \Modules\Inventory\Entities\Item::create($data);
        return response()->json(['saved' => $item], 201);
    }
}
