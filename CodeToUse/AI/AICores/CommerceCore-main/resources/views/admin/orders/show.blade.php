<x-layouts.admin>
    <x-slot:header>Order {{ $order->order_number }}</x-slot:header>

    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Order Header --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white flex items-center flex-wrap gap-2">
                        {{ $order->order_number }}
                        <a href="{{ route('admin.orders.invoice', [$order, 'a4']) }}" class="text-xs bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 px-3 py-1.5 rounded-md flex items-center gap-1.5 transition-colors font-medium border border-slate-200 dark:border-slate-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Print Invoice
                        </a>
                        <a href="{{ route('admin.orders.invoice', [$order, 'thermal']) }}" class="text-xs bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 px-3 py-1.5 rounded-md flex items-center gap-1.5 transition-colors font-medium border border-slate-200 dark:border-slate-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Thermal Receipt
                        </a>
                        <a href="{{ route('admin.returns.create', ['order_id' => $order->id]) }}" class="text-xs bg-red-50 hover:bg-red-100 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 px-3 py-1.5 rounded-md flex items-center gap-1.5 transition-colors font-semibold border border-red-200 dark:border-red-800/30">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                            Initiate Return
                        </a>
                    </h2>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-2">Placed {{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
                </div>
                <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="flex items-center gap-2">
                    @csrf @method('PUT')
                    <select name="status" class="px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-sm outline-none">
                        @foreach(['pending', 'paid', 'shipped', 'delivered', 'cancelled'] as $s)
                        <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">Update</button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Customer Info --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Customer Details</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/50 pb-3">
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Name</span>
                        <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $order->customer_name }}</span>
                    </div>
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/50 pb-3">
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Email</span>
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $order->customer_email }}</span>
                    </div>
                    @if($order->phone)
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/50 pb-3">
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Phone</span>
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $order->phone }}</span>
                    </div>
                    @endif
                    @if($order->address)
                    <div class="flex flex-col gap-1 pt-1">
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Shipping Address</span>
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $order->address }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Order Summary --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Financial Summary</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-700/50 pb-3">
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Subtotal</span>
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200">${{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-700/50 pb-3">
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Tax</span>
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200">${{ number_format($order->tax, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-2">
                        <span class="text-base font-bold text-slate-900 dark:text-white">Total</span>
                        <span class="text-xl font-black text-blue-600 dark:text-blue-400">${{ number_format($order->total_price, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Shipment Tracking --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Shipment & Logistics</h3>
            
            @if($order->shipment)
                <div class="flex items-center justify-between p-5 bg-slate-50 dark:bg-slate-900/50 rounded-lg border border-slate-200 dark:border-slate-700">
                    <div>
                        <p class="text-sm font-bold text-slate-900 dark:text-white mb-1">{{ $order->shipment->courier->name ?? 'Courier' }}</p>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            Tracking ID: 
                            <span class="font-mono text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2 py-0.5 rounded">{{ $order->shipment->tracking_number ?? 'N/A' }}</span>
                        </p>
                    </div>
                    <div class="text-right">
                        @php
                        $shipColors = [
                            'picked' => 'bg-slate-200 text-slate-800 dark:bg-slate-700 dark:text-slate-300',
                            'in_transit' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-400',
                            'delivered' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
                            'returned' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-400',
                            'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-500',
                        ];
                        $shipColor = $shipColors[$order->shipment->status] ?? 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold {{ $shipColor }}">
                            {{ ucfirst(str_replace('_', ' ', $order->shipment->status)) }}
                        </span>
                        <p class="text-xs font-medium text-slate-400 mt-2">Cost: ${{ number_format($order->shipment->shipping_cost, 2) }}</p>
                    </div>
                </div>
                
                <form action="{{ route('admin.shipments.status', $order->shipment) }}" method="POST" class="mt-4 flex gap-3">
                    @csrf
                    <select name="status" class="flex-1 px-3 py-2 text-sm border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 rounded-lg dark:bg-slate-900 dark:border-slate-700 dark:text-white outline-none shadow-sm font-medium">
                        @foreach(['picked', 'in_transit', 'delivered', 'returned', 'cancelled'] as $s)
                            <option value="{{ $s }}" {{ $order->shipment->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">Update Status</button>
                </form>
            @else
                <form action="{{ route('admin.orders.dispatch', $order) }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Select Courier</label>
                            <select name="courier_id" required class="w-full px-3 py-2 text-sm border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 rounded-lg dark:bg-slate-900 dark:border-slate-700 dark:text-white outline-none shadow-sm transition-colors">
                                <option value="">Choose a courier...</option>
                                @foreach($couriers as $courier)
                                    <option value="{{ $courier->id }}">{{ $courier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Tracking Number</label>
                            <input type="text" name="tracking_number" placeholder="e.g. TRK-987654321" class="w-full px-3 py-2 text-sm border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 rounded-lg dark:bg-slate-900 dark:border-slate-700 dark:text-white outline-none shadow-sm transition-colors">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Shipping Cost ($)</label>
                            <input type="number" step="0.01" name="shipping_cost" value="0.00" class="w-full px-3 py-2 text-sm border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 rounded-lg dark:bg-slate-900 dark:border-slate-700 dark:text-white outline-none shadow-sm transition-colors">
                        </div>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition-colors shadow-sm">
                        Dispatch Shipment
                    </button>
                </form>
            @endif
        </div>

        {{-- Order Items --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300">Order Items ({{ $order->items->count() }})</h3>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700/50">
                @foreach($order->items as $item)
                <div class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <div class="w-16 h-16 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 overflow-hidden flex-shrink-0">
                        @if($item->product->image)
                        <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                        @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $item->product->name }}</p>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Qty: {{ $item->quantity }} &times; ${{ number_format($item->price, 2) }}</p>
                    </div>
                    <p class="text-base font-bold text-slate-900 dark:text-white">${{ number_format($item->price * $item->quantity, 2) }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.admin>
