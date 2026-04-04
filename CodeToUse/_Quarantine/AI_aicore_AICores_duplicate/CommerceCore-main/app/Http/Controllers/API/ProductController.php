<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use App\Services\CurrencyService;

class ProductController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function index(Request $request, $storeDomain)
    {
        $store = Store::where('domain', $storeDomain)->firstOrFail();
        
        $products = Product::where('store_id', $store->id)
                           ->when($request->category, function ($q) use ($request) {
                               return $q->where('category', $request->category);
                           })
                           ->paginate(15);
                           
        // Map localizations & currency handling mock
        $products->getCollection()->transform(function($product) {
             // Basic localized price conversion on the fly
             $product->localized_price = $this->currencyService->convert($product->price);
             $product->currency_symbol = $this->currencyService->symbol();
             return $product;
        });

        return response()->json($products);
    }

    public function show($storeDomain, $id)
    {
        $store = Store::where('domain', $storeDomain)->firstOrFail();
        $product = Product::where('store_id', $store->id)->findOrFail($id);

        $product->localized_price = $this->currencyService->convert($product->price);
        $product->currency_symbol = $this->currencyService->symbol();

        return response()->json($product);
    }
}
