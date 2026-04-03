<?php

namespace Modules\FieldItems\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ItemPricingController extends Controller
{
    public function preview(Request $request)
    {
        $cost = (float) $request->input('cost', 0);
        $markup = (float) $request->input('markup_percent', 0);
        $price = $cost + ($cost * $markup / 100.0);
        return response()->json(['price' => round($price, 2)]);
    }
}
