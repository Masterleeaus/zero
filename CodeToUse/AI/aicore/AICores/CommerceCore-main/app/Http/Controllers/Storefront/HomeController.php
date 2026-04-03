<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Product;
use App\Models\Category;
use App\Services\BuilderService;
use App\Services\ProductService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(
        private BuilderService $builderService,
        private ProductService $productService
    ) {}

    public function index(Request $request, string $store)
    {
        $store = Store::where('slug', $store)->where('status', 'active')->firstOrFail();
        
        $cacheKey = "storefront:{$store->id}:homepage";
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($store) {
            $store->load('settings.theme');
            $homepage = $this->builderService->getHomepage($store);
            $featuredProducts = Product::where('store_id', $store->id)
                ->where('status', 'active')
                ->where('featured', true)
                ->take(8)
                ->get();

            $categories = Category::where('store_id', $store->id)
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->take(6)
                ->get();

            return view('storefront.home', compact('store', 'homepage', 'featuredProducts', 'categories'))->render();
        });
    }

    public function products(Request $request, string $store)
    {
        $store = Store::where('slug', $store)->where('status', 'active')->firstOrFail();

        $products = $this->productService->getForStore($store, [
            'status' => 'active',
            'category_id' => $request->category,
            'search' => $request->search,
            'min_price' => $request->min_price,
            'max_price' => $request->max_price,
            'sort' => $request->sort ?? 'created_at',
            'direction' => $request->direction ?? 'desc',
        ]);

        $categories = Category::where('store_id', $store->id)
            ->where('is_active', true)
            ->get();

        return view('storefront.products', compact('store', 'products', 'categories'));
    }

    public function productDetail(string $store, string $product)
    {
        $store = Store::where('slug', $store)->where('status', 'active')->firstOrFail();
        $product = Product::where('store_id', $store->id)
            ->where('slug', $product)
            ->where('status', 'active')
            ->firstOrFail();

        $relatedProducts = Product::where('store_id', $store->id)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->take(4)
            ->get();

        return view('storefront.product-detail', compact('store', 'product', 'relatedProducts'));
    }
}
