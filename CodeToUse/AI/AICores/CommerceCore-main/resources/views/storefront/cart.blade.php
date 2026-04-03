<x-layouts.storefront :store="$storeModel">
    <div class="max-w-5xl mx-auto px-6 lg:px-8 py-12 animate-fade-in">
        <div class="flex flex-col md:flex-row justify-between items-end gap-6 mb-12">
            <div>
                <nav class="flex items-center gap-2 mb-4">
                    <a href="{{ route('storefront.home', $store->slug) }}" class="text-[10px] font-black text-surface-400 uppercase tracking-widest hover:text-primary-500 transition-colors">Home</a>
                    <svg class="w-2.5 h-2.5 text-surface-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="text-[10px] font-black text-primary-500 uppercase tracking-widest">Cart Registry</span>
                </nav>
                <h1 class="text-4xl sm:text-5xl font-display font-black text-surface-900 dark:text-white leading-none tracking-tight">Shopping Cart</h1>
            </div>
            @if(count($items))
            <p class="text-surface-500 dark:text-surface-400 font-bold uppercase text-[11px] tracking-widest bg-surface-50 dark:bg-surface-800/50 px-4 py-2 rounded-full border border-surface-100 dark:border-surface-700/50">
                {{ count($items) }} Nodes Synchronized
            </p>
            @endif
        </div>

        @if(count($items))
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            {{-- Cart Items --}}
            <div class="lg:col-span-8 space-y-6">
                @foreach($items as $item)
                <div class="group relative flex flex-col sm:flex-row items-center gap-8 bg-white dark:bg-surface-800/40 backdrop-blur-md rounded-[2.5rem] border border-surface-100 dark:border-surface-700/50 p-6 sm:p-8 shadow-2xl shadow-black/5 hover:shadow-primary-500/5 transition-all animate-slide-up" style="animation-delay: {{ $loop->index * 0.1 }}s">
                    <div class="w-32 h-32 rounded-3xl bg-surface-50 dark:bg-surface-800 overflow-hidden border border-surface-100 dark:border-surface-700/50 flex-shrink-0 group-hover:scale-105 transition-transform duration-700">
                        @if($item['product']->image)
                        <img src="{{ asset('storage/' . $item['product']->image) }}" alt="" class="w-full h-full object-cover">
                        @else
                        <div class="w-full h-full flex items-center justify-center"><svg class="w-12 h-12 text-surface-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
                        @endif
                    </div>
                    
                    <div class="flex-1 text-center sm:text-left">
                        <h3 class="text-lg font-black text-surface-900 dark:text-white group-hover:text-primary-500 transition-colors">{{ $item['product']->name }}</h3>
                        <p class="text-sm font-display font-black text-primary-500 mt-1">${{ number_format($item['product']->price, 2) }}</p>
                        
                        <div class="mt-6 flex flex-wrap items-center justify-center sm:justify-start gap-4">
                            <form method="POST" action="{{ route('storefront.cart.update', $store) }}" class="flex items-center bg-surface-50 dark:bg-surface-800 rounded-2xl p-1 border border-surface-100 dark:border-surface-700/50">
                                @csrf @method('PUT')
                                <input type="hidden" name="product_id" value="{{ $item['product']->id }}">
                                <button type="button" onclick="this.nextElementSibling.stepDown(); this.form.submit()" class="w-10 h-10 flex items-center justify-center text-surface-400 hover:text-primary-500 transition-colors">−</button>
                                <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="0" class="w-12 text-center border-0 bg-transparent text-sm font-black dark:text-white focus:ring-0">
                                <button type="button" onclick="this.previousElementSibling.stepUp(); this.form.submit()" class="w-10 h-10 flex items-center justify-center text-surface-400 hover:text-primary-500 transition-colors">+</button>
                            </form>
                            
                            <form method="POST" action="{{ route('storefront.cart.remove', [$store, $item['product']->id]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-[10px] font-black text-rose-500 uppercase tracking-widest hover:text-rose-600 transition-colors flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Remove Node
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="text-center sm:text-right pt-6 sm:pt-0 border-t sm:border-t-0 border-surface-100 dark:border-surface-700/50 w-full sm:w-auto">
                        <p class="text-[10px] font-black text-surface-400 uppercase tracking-widest mb-1">Subtotal Payload</p>
                        <p class="text-2xl font-display font-black text-surface-900 dark:text-white">${{ number_format($item['total'], 2) }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Cart Summary --}}
            <div class="lg:col-span-4">
                <div class="bg-surface-900 border border-white/5 rounded-[2.5rem] p-8 sm:p-10 sticky top-24 shadow-[0_20px_50px_rgba(0,0,0,0.3)] animate-slide-up text-white" style="animation-delay: 0.4s">
                    <h2 class="text-[12px] font-black text-primary-400 uppercase tracking-[0.2em] mb-10 flex items-center gap-3">
                        <span class="w-8 h-[1px] bg-primary-400/30"></span>
                        Registry Summary
                    </h2>

                    <div class="space-y-6 pt-2">
                        <div class="flex justify-between text-[11px] font-bold uppercase tracking-widest">
                            <span class="text-surface-400">Total Items</span>
                            <span class="text-white">{{ $items->sum('quantity') }}</span>
                        </div>
                        <div class="flex justify-between text-[11px] font-bold uppercase tracking-widest">
                            <span class="text-surface-400">Node Transmission</span>
                            <span class="text-emerald-400">FREE</span>
                        </div>
                        
                        <div class="pt-8 mt-8 border-t border-white/10 flex justify-between items-end">
                            <div>
                                <p class="text-[10px] font-black text-surface-500 uppercase tracking-widest mb-2">Total Payload</p>
                                <p class="text-4xl font-display font-black text-white leading-none tracking-tighter">${{ number_format($total, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('storefront.checkout', $store) }}" class="w-full h-16 mt-10 bg-primary-600 hover:bg-primary-500 text-white font-display font-black rounded-2xl shadow-2xl shadow-primary-500/20 active:scale-95 transition-all flex items-center justify-center gap-4 text-lg uppercase tracking-[0.2em]">
                        PROCEED TO DEPLOY
                        <svg class="w-6 h-6 animate-bounce-horizontal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                </div>
            </div>
        </div>
        @else
        <div class="text-center py-32 bg-surface-50 dark:bg-surface-800/40 rounded-[3rem] border border-dashed border-surface-200 dark:border-surface-700/50 animate-fade-in">
            <div class="w-24 h-24 bg-surface-200/50 dark:bg-surface-700/50 rounded-full flex items-center justify-center mx-auto mb-8">
                <svg class="w-12 h-12 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <h2 class="text-3xl font-display font-black text-surface-900 dark:text-white mb-4">Registry Inactive</h2>
            <p class="text-surface-500 dark:text-surface-400 font-medium max-w-sm mx-auto mb-10">Your configuration registry is currently empty. Synchronize items from the catalog to proceed.</p>
            <a href="{{ route('storefront.products', $store) }}" class="inline-flex h-14 items-center gap-3 px-10 bg-surface-900 dark:bg-primary-600 hover:bg-black dark:hover:bg-primary-500 text-white font-display font-black rounded-2xl transition-all shadow-xl shadow-primary-500/20 uppercase tracking-widest text-sm">
                Explore Catalog
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
        @endif
    </div>
</x-layouts.storefront>
