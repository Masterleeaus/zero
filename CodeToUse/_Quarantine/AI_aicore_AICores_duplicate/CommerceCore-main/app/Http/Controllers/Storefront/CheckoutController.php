<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\Coupon;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Services\IntelligenceService;
use App\Models\LoyaltyPoint;
use App\Models\AbandonedCart;

class CheckoutController extends Controller
{
    public function __construct(
        private NotificationService $notificationService,
        private IntelligenceService $intelligenceService
    ) {}

    public function index($store)
    {
        $store = Store::where('slug', $store)->firstOrFail();
        $cart = session()->get('cart', []);
        
        if (empty($cart)) return redirect()->route('storefront.home', $store->slug);
        
        // Track for Abandoned Cart
        AbandonedCart::updateOrCreate(
            ['email' => Auth::check() ? Auth::user()->email : session('guest_email', 'unknown')],
            [
                'cart_data' => $cart,
                'last_active_at' => now(),
                'is_recovered' => false
            ]
        );

        $items = [];
        $total = 0;
        foreach ($cart as $id => $details) {
            $product = Product::find($id);
            if ($product) {
                $itemTotal = $product->price * $details['quantity'];
                $items[] = [
                    'product' => $product,
                    'quantity' => $details['quantity'],
                    'total' => $itemTotal
                ];
                $total += $itemTotal;
            }
        }

        return view('storefront.checkout', [
            'store' => $store->slug,
            'storeModel' => $store,
            'items' => $items,
            'total' => $total
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'phone' => 'required|string',
            'address' => 'required|string',
            'payment_method' => 'required|in:cod,prepaid',
            'notes' => 'nullable|string',
            'coupon_code' => 'nullable|string'
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) return back()->with('error', 'Cart is empty');

        $store = Store::where('slug', session('store_slug'))->firstOrFail();

        // Coupon Logic
        $discount = 0;
        $coupon = null;
        if ($validated['coupon_code']) {
            $coupon = Coupon::where('code', $validated['coupon_code'])
                ->where('store_id', $store->id)
                ->where('is_active', true)
                ->where(function($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })->first();

            if ($coupon) {
                $subtotal = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);
                if ($subtotal >= $coupon->min_spend) {
                    $discount = ($coupon->type === 'percentage') 
                        ? ($subtotal * ($coupon->value / 100)) 
                        : $coupon->value;
                }
            }
        }

        return DB::transaction(function() use ($validated, $cart, $store, $discount, $coupon) {
            $subtotal = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);
            $total = max(0, $subtotal - $discount);
            
            // Intelligence Layer: Calculate Fraud Score
            $score = $this->intelligenceService->calculateFraudScore(new Order([
                'total_price' => $total,
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'shipping_address' => ['billing' => $validated['address']], // simplistic for score calc
            ]));

            $order = Order::create([
                'store_id' => $store->id,
                'user_id' => Auth::id(),
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'status' => 'pending',
                'subtotal' => $subtotal,
                'total_price' => $total,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'lifecycle_status' => 'NEW',
                'requires_confirmation' => $validated['payment_method'] === 'cod',
                'checkout_notes' => ($validated['notes'] ?? '') . ($coupon ? " (Coupon: {$coupon->code})" : ""),
                'is_confirmed' => false,
                'fraud_score' => $score,
                'risk_explanation' => $score > 50 ? 'High risk detected by AI heuristic pattern matching.' : 'Normal risk profile.'
            ]);

            foreach ($cart as $id => $details) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $id,
                    'quantity' => $details['quantity'],
                    'price' => $details['price']
                ]);
            }

            if ($coupon) {
                $coupon->increment('used_count');
            }

            // Earn Loyalty Points (1 point per $10)
            if (Auth::check()) {
                $points = floor($total / 10);
                if ($points > 0) {
                    LoyaltyPoint::create([
                        'user_id' => Auth::id(),
                        'points' => $points,
                        'type' => 'earn',
                        'reason' => "Earned from order {$order->order_number}"
                    ]);
                    Auth::user()->increment('loyalty_points', $points);
                }
            }

            // Mark cart as recovered if applicable
            AbandonedCart::where('email', $validated['customer_email'])->update(['is_recovered' => true]);

            // Trigger Confirmation if COD via Redirect to WhatsApp
            if ($order->requires_confirmation) {
                // Log notification
                $this->notificationService->send(
                    $order->user_id,
                    'whatsapp',
                    $order->phone,
                    "Order confirmation requested for {$order->order_number}"
                );

                // Prepare WhatsApp Redirect
                $message = "Confirming order #{$order->order_number} for {$order->total_price}. Ship to: {$order->address}";
                $whatsappUrl = "https://wa.me/{$store->phone}?text=" . urlencode($message);
                
                // Clear cart anyway
                session()->forget('cart');

                return redirect($whatsappUrl);
            }

            session()->forget('cart');

            return redirect()->route('storefront.order.success', ['store' => $store->slug, 'order_number' => $order->order_number]);
        });
    }

    public function success($store, $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        return view('storefront.success', compact('order'));
    }

    public function confirm($store, $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $order->update(['is_confirmed' => true, 'status' => 'processing']);
        
        return view('storefront.confirmed', compact('order'));
    }
}
