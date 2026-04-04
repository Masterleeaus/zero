<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private OrderService $orderService
    ) {}

    public function index(string $store)
    {
        $storeModel = Store::where('slug', $store)->where('status', 'active')->firstOrFail();
        $items = $this->cartService->getItems($store);
        $total = $this->cartService->getTotal($store);

        return view('storefront.cart', compact('storeModel', 'items', 'total', 'store'));
    }

    public function add(Request $request, string $store)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1',
            'purchase_type' => 'nullable|string|in:onetime,subscription'
        ]);

        $this->cartService->add(
            $store, 
            $validated['product_id'], 
            $validated['quantity'] ?? 1,
            $validated['purchase_type'] ?? 'onetime'
        );

        // Flash Pixel AddToCart Event
        session()->flash('pixel_event', [
            'name' => 'AddToCart',
            'data' => [
                'content_ids' => [$validated['product_id']],
                'content_type' => 'product',
                'value' => \App\Models\Product::find($validated['product_id'])->price * ($validated['quantity'] ?? 1),
                'currency' => \App\Models\Store::where('slug', $store)->first()?->settings?->getSetting('currency', 'USD') ?? 'USD',
            ]
        ]);

        return redirect()->back()->with('success', 'Added to cart!');
    }

    public function update(Request $request, string $store)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
        ]);

        $this->cartService->update($store, $validated['product_id'], $validated['quantity']);

        return redirect()->back()->with('success', 'Cart updated!');
    }

    public function remove(string $store, int $productId)
    {
        $this->cartService->remove($store, $productId);
        return redirect()->back()->with('success', 'Item removed!');
    }

    public function checkout(string $store)
    {
        $storeModel = Store::where('slug', $store)->where('status', 'active')->firstOrFail();
        $items = $this->cartService->getItems($store);
        $total = $this->cartService->getTotal($store);

        if (empty($items)) {
            return redirect()->route('storefront.cart', $store)
                ->with('error', 'Your cart is empty!');
        }

        return view('storefront.checkout', compact('storeModel', 'items', 'total', 'store'));
    }

    public function placeOrder(Request $request, string $store, \App\Services\FraudDetectionService $fraudDetectionService)
    {
        $storeModel = Store::where('slug', $store)->where('status', 'active')->firstOrFail();

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $cartItems = $this->cartService->toOrderItems($store);
        $total = $this->cartService->getTotal($store);

        if (empty($cartItems)) {
            return redirect()->route('storefront.cart', $store)
                ->with('error', 'Your cart is empty!');
        }

        // --- FRAUD DETECTION CHECK ---
        if (!empty($validated['phone'])) {
            $fraudErrors = $fraudDetectionService->evaluateOrder($storeModel, $validated['phone'], $total);
            
            if (count($fraudErrors) > 0) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['fraud_alert' => implode(' ', $fraudErrors)]);
            }
        }

        $validated['user_id'] = auth()->id();

        $order = $this->orderService->create($storeModel, $validated, $cartItems);
        $this->cartService->clear($store);

        // Flash Pixel Purchase Event
        session()->flash('pixel_event', [
            'name' => 'Purchase',
            'data' => [
                'value' => $total,
                'currency' => $storeModel->settings?->getSetting('currency', 'USD') ?? 'USD',
                'content_type' => 'product',
                'order_id' => $order->id
            ]
        ]);

        return redirect()->route('storefront.order.success', [$store, $order->order_number]);
    }

    public function orderSuccess(string $store, string $orderNumber)
    {
        $storeModel = Store::where('slug', $store)->firstOrFail();
        $order = $storeModel->orders()->where('order_number', $orderNumber)->with('items.product')->firstOrFail();

        return view('storefront.order-success', compact('storeModel', 'order', 'store'));
    }
}
