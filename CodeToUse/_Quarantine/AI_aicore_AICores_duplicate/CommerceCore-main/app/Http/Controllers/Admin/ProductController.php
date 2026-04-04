<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Services\ProductService;
use App\Traits\ResolvesStore;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ResolvesStore;

    public function __construct(private ProductService $productService) {}

    public function index(Request $request)
    {
        $store = $this->getActiveStore($request);

        $products = $this->productService->getForStore($store, [
            'search'      => $request->search,
            'status'      => $request->status,
            'category_id' => $request->category_id,
        ]);

        $categories = Category::where('store_id', $store->id)->get();

        $topProducts = Product::where('store_id', $store->id)
            ->withCount('orderItems')
            ->orderBy('order_items_count', 'desc')
            ->take(5)
            ->get();

        $stockStats = [
            'in_stock'     => Product::where('store_id', $store->id)->where('stock', '>', 10)->count(),
            'low_stock'    => Product::where('store_id', $store->id)->whereBetween('stock', [1, 10])->count(),
            'out_of_stock' => Product::where('store_id', $store->id)->where('stock', '<=', 0)->count(),
        ];

        return view('admin.products.index', compact('products', 'store', 'categories', 'topProducts', 'stockStats'));
    }

    public function create(Request $request)
    {
        $store      = $this->getActiveStore($request);
        $categories = Category::where('store_id', $store->id)->get();
        return view('admin.products.create', compact('store', 'categories'));
    }

    public function store(Request $request)
    {
        $store = $this->getActiveStore($request);

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'compare_price'=> 'nullable|numeric|min:0',
            'sku'          => 'nullable|string|max:255',
            'stock'        => 'required|integer|min:0',
            'category_id'  => 'nullable|exists:categories,id',
            'image'        => 'nullable|image|max:2048',
            'status'       => 'required|in:active,draft,archived',
            'featured'     => 'boolean',
        ]);

        $validated['featured'] = $request->boolean('featured');
        $this->productService->create($store, $validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully!');
    }

    public function edit(Request $request, Product $product)
    {
        // ✅ Authorization: ensure product belongs to active store
        if (!$request->user()->isSuperAdmin()) {
            $store = $this->getActiveStore($request);
            if ($product->store_id !== $store->id) {
                abort(403, 'You do not have access to this product.');
            }
        }

        $categories = Category::where('store_id', $product->store_id)->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        // ✅ Authorization: ensure product belongs to active store
        if (!$request->user()->isSuperAdmin()) {
            $store = $this->getActiveStore($request);
            if ($product->store_id !== $store->id) {
                abort(403, 'You do not have access to this product.');
            }
        }

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'compare_price'=> 'nullable|numeric|min:0',
            'sku'          => 'nullable|string|max:255',
            'stock'        => 'required|integer|min:0',
            'category_id'  => 'nullable|exists:categories,id',
            'image'        => 'nullable|image|max:2048',
            'status'       => 'required|in:active,draft,archived',
            'featured'     => 'boolean',
        ]);

        $validated['featured'] = $request->boolean('featured');
        $this->productService->update($product, $validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully!');
    }

    public function destroy(Request $request, Product $product)
    {
        // ✅ Authorization: ensure product belongs to active store
        if (!$request->user()->isSuperAdmin()) {
            $store = $this->getActiveStore($request);
            if ($product->store_id !== $store->id) {
                abort(403, 'You do not have access to this product.');
            }
        }

        $this->productService->delete($product);
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted!');
    }
}
