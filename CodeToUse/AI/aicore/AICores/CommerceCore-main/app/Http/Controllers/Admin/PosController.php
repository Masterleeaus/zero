<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Store;
use App\Models\PosHeldOrder;
use App\Traits\ResolvesStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    use ResolvesStore;

    /**
     * Display the POS interface — products scoped to the active store.
     */
    public function index(Request $request)
    {
        $store   = $this->getActiveStore($request);
        $storeId = $store->id;

        $categories = Category::where('store_id', $storeId)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        $products = Product::where('store_id', $storeId)
            ->where('status', 'active')
            ->orderBy('name')
            ->paginate(50);

        return view('admin.pos.index', compact('categories', 'products', 'store'));
    }

    /**
     * Process a POS Checkout — store_id resolved securely.
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'customer_id'      => 'nullable|exists:users,id',
            'customer_name'    => 'nullable|string|max:255',
            'customer_phone'   => 'nullable|string|max:20',
            'payment_method'   => 'required|string|in:cash,card,bank_transfer',
            'amount_paid'      => 'required|numeric|min:0',
            'items'            => 'required|array',
            'items.*.id'       => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price'    => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $store   = $this->getActiveStore($request);
            $storeId = $store->id;

            $totalAmount = 0;
            foreach ($validated['items'] as $itemData) {
                $totalAmount += $itemData['price'] * $itemData['quantity'];

                $product = Product::where('store_id', $storeId)->findOrFail($itemData['id']);

                if ($product->stock < $itemData['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }
                $product->decrement('stock', $itemData['quantity']);
            }

            $order = Order::create([
                'store_id'         => $storeId,
                'user_id'          => auth()->id(),
                'customer_id'      => $validated['customer_id'] ?? null,
                'order_number'     => 'POS-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
                'customer_name'    => $validated['customer_name'] ?? 'Walk-in Customer',
                'customer_email'   => null,
                'phone'            => $validated['customer_phone'] ?? null,
                'address'          => 'In-Store POS',
                'subtotal'         => $totalAmount,
                'tax'              => 0.00,
                'total_price'      => $totalAmount,
                'status'           => 'paid',
                'lifecycle_status' => 'DELIVERED',
                'notes'            => 'POS Sale - ' . ucfirst($validated['payment_method']),
            ]);

            foreach ($validated['items'] as $itemData) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $itemData['id'],
                    'quantity'   => $itemData['quantity'],
                    'price'      => $itemData['price'],
                ]);
            }

            DB::commit();

            return response()->json([
                'status'       => 'success',
                'message'      => 'Order processed successfully.',
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get today's POS transaction history — scoped to store.
     */
    public function posHistory(Request $request)
    {
        $store = $this->getActiveStore($request);

        $history = Order::where('store_id', $store->id)
            ->whereDate('created_at', today())
            ->with(['items'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($order) => [
                'id'           => $order->id,
                'order_number' => $order->order_number,
                'total_price'  => $order->total_price,
                'items_count'  => $order->items->sum('quantity'),
                'time'         => $order->created_at->format('H:i'),
                'status'       => $order->status,
            ]);

        return response()->json($history);
    }

    /**
     * Get held orders — scoped to store.
     */
    public function heldOrders(Request $request)
    {
        $store = $this->getActiveStore($request);

        $heldOrders = PosHeldOrder::where('store_id', $store->id)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($heldOrders);
    }

    /**
     * Hold the current cart — scoped to store.
     */
    public function holdOrder(Request $request)
    {
        $validated = $request->validate([
            'reference'     => 'required|string|max:255',
            'cart_data'     => 'required|array',
            'customer_data' => 'nullable|array',
            'subtotal'      => 'required|numeric',
        ]);

        $store = $this->getActiveStore($request);

        if (PosHeldOrder::where('store_id', $store->id)->where('reference', $validated['reference'])->exists()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'An order with this reference is already on hold. Please use a different name.',
            ], 422);
        }

        $heldOrder = PosHeldOrder::create([
            'store_id'      => $store->id,
            'user_id'       => auth()->id(),
            'reference'     => $validated['reference'],
            'cart_data'     => $validated['cart_data'],
            'customer_data' => $validated['customer_data'] ?? [],
            'subtotal'      => $validated['subtotal'],
            'total'         => $validated['subtotal'],
        ]);

        return response()->json([
            'status'      => 'success',
            'message'     => 'Order put on hold.',
            'held_order'  => $heldOrder,
        ]);
    }

    /**
     * Recall a held order — with store ownership check.
     */
    public function recallOrder(Request $request, PosHeldOrder $heldOrder)
    {
        $store = $this->getActiveStore($request);
        if ($heldOrder->store_id !== $store->id) {
            abort(403, 'This held order does not belong to your store.');
        }

        $data = [
            'cart_data'     => $heldOrder->cart_data,
            'customer_data' => $heldOrder->customer_data,
        ];

        $heldOrder->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Order recalled successfully.',
            'data'    => $data,
        ]);
    }

    /**
     * Delete a held order — with store ownership check.
     */
    public function deleteHeldOrder(Request $request, PosHeldOrder $heldOrder)
    {
        $store = $this->getActiveStore($request);
        if ($heldOrder->store_id !== $store->id) {
            abort(403, 'This held order does not belong to your store.');
        }

        $heldOrder->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Held order discarded.',
        ]);
    }

    /**
     * Search for customers in the active store.
     */
    public function searchCustomers(Request $request)
    {
        $query = $request->query('q');
        $store = $this->getActiveStore($request);

        $customers = \App\Models\User::where('store_id', $store->id)
            ->where('role', 'customer')
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'phone', 'email']);

        return response()->json($customers);
    }

    /**
     * Register a new customer on the fly in POS.
     */
    public function registerCustomer(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $store = $this->getActiveStore($request);

        // Check if customer already exists by phone or email in this store
        $exists = \App\Models\User::where('store_id', $store->id)
            ->where('role', 'customer')
            ->where(function($q) use ($validated) {
                if ($validated['phone']) $q->orWhere('phone', $validated['phone']);
                if ($validated['email']) $q->orWhere('email', $validated['email']);
            })
            ->first();

        if ($exists) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Customer with this phone or email already exists.',
                'customer' => $exists
            ], 422);
        }

        $customer = \App\Models\User::create([
            'store_id' => $store->id,
            'name'     => $validated['name'],
            'phone'    => $validated['phone'],
            'email'    => $validated['email'] ?? 'cust_' . Str::random(8) . '@example.com', // Placeholder if blank
            'password' => bcrypt(Str::random(16)),
            'role'     => 'customer',
        ]);

        return response()->json([
            'status'   => 'success',
            'message'  => 'Customer registered successfully.',
            'customer' => $customer
        ]);
    }
}
