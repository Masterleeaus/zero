<x-layouts.storefront :store="$storeModel">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
        <div class="w-20 h-20 mx-auto rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center mb-6 animate-scale-in">
            <svg class="w-10 h-10 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1 class="text-3xl font-display font-bold text-surface-800 dark:text-white animate-fade-in">Order Placed Successfully!</h1>
        <p class="mt-3 text-surface-500 animate-slide-up">Thank you for your purchase. Your order has been confirmed.</p>

        <div class="mt-8 bg-white dark:bg-surface-800 rounded-2xl border border-surface-200 dark:border-surface-700 p-6 text-left">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm text-surface-500">Order Number</span>
                <span class="font-display font-bold text-primary-600 dark:text-primary-400">{{ $order->order_number }}</span>
            </div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm text-surface-500">Status</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold badge-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
            </div>
            <div class="border-t border-surface-200 dark:border-surface-700 pt-4 space-y-2">
                @foreach($order->items as $item)
                <div class="flex justify-between text-sm">
                    <span class="text-surface-600 dark:text-surface-300">{{ $item->product->name }} × {{ $item->quantity }}</span>
                    <span class="font-medium text-surface-800 dark:text-white">${{ number_format($item->price * $item->quantity, 2) }}</span>
                </div>
                @endforeach
            </div>
            <div class="flex justify-between mt-4 pt-4 border-t border-surface-200 dark:border-surface-700">
                <span class="font-display font-bold text-surface-800 dark:text-white">Total</span>
                <span class="font-display font-bold gradient-text text-lg">${{ number_format($order->total_price, 2) }}</span>
            </div>
        </div>

        <a href="{{ route('storefront.products', $store) }}" class="inline-flex items-center gap-2 mt-8 px-6 py-3 bg-primary-500 hover:bg-primary-600 text-white font-semibold rounded-xl transition-colors">
            Continue Shopping
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>
</x-layouts.storefront>
