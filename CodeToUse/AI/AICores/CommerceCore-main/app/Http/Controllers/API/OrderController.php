<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::where('customer_id', $request->user()->id)
                       ->with('items.product')
                       ->orderByDesc('created_at')
                       ->paginate(10);
                       
        return response()->json($orders);
    }
    
    public function show(Request $request, $id)
    {
        $order = Order::where('customer_id', $request->user()->id)
                      ->with('items.product')
                      ->findOrFail($id);
                      
        return response()->json($order);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();
            
            $totalAmount = 0;
            foreach ($validatedData['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            $order = Order::create([
                'store_id' => $validatedData['store_id'],
                'customer_id' => $request->user()->id,
                'order_number' => 'APP-' . strtoupper(uniqid()),
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => 'unpaid'
            ]);

            foreach ($validatedData['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Order placed successfully', 'order' => $order], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to place order', 'error' => $e->getMessage()], 500);
        }
    }
}
