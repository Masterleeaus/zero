<x-layouts.storefront>
    <div class="max-w-7xl mx-auto px-6 py-12">
        <div class="bg-white dark:bg-surface-800 rounded-3xl p-12 text-center shadow-xl border border-surface-100 dark:border-surface-700">
            <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h1 class="text-3xl font-black text-surface-900 dark:text-white mb-4">Order Received!</h1>
            <p class="text-surface-500 mb-8 max-w-md mx-auto">Your order <span class="font-bold text-indigo-600">#{{ $order->order_number }}</span> has been placed successfully.</p>

            @if($order->requires_confirmation && !$order->is_confirmed)
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 p-6 rounded-2xl mb-8 max-w-lg mx-auto">
                <h4 class="text-amber-800 dark:text-amber-200 font-bold mb-2">COD Confirmation Required</h4>
                <p class="text-xs text-amber-700/80">We've sent a confirmation message to <span class="font-bold">{{ $order->phone }}</span>. Please confirm your order to start processing.</p>
            </div>
            @endif

            <div class="flex justify-center gap-4">
                <a href="/" class="px-8 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all">Back to Home</a>
                <a href="#" class="px-8 py-3 border border-surface-200 text-surface-700 dark:text-surface-300 font-bold rounded-xl hover:bg-surface-50 transition-all">Track Order</a>
            </div>
        </div>
    </div>
</x-layouts.storefront>
