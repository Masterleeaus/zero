<x-layouts.storefront :store="$storeModel">
    <div class="max-w-6xl mx-auto px-6 lg:px-8 py-12 animate-fade-in">
        <nav class="flex items-center gap-2 mb-10 overflow-x-auto whitespace-nowrap pb-2">
            <a href="{{ route('storefront.home', $store->slug) }}" class="text-[10px] font-black text-surface-400 uppercase tracking-widest hover:text-primary-500 transition-colors">Storefront</a>
            <svg class="w-2.5 h-2.5 text-surface-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('storefront.cart', $store->slug) }}" class="text-[10px] font-black text-surface-400 uppercase tracking-widest hover:text-primary-500 transition-colors">Cart Registry</a>
            <svg class="w-2.5 h-2.5 text-surface-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-[10px] font-black text-primary-500 uppercase tracking-widest">Secure Checkout</span>
        </nav>

        <div class="flex flex-col lg:flex-row justify-between items-end gap-6 mb-12">
            <div>
                <h1 class="text-4xl sm:text-5xl font-display font-black text-surface-900 dark:text-white leading-none tracking-tight">Checkout</h1>
                <p class="text-surface-500 dark:text-surface-400 font-medium mt-3">Execute your order through our secure infrastructure.</p>
            </div>
            <div class="hidden sm:flex items-center gap-4">
                <div class="flex -space-x-3">
                    <div class="w-10 h-10 rounded-full border-2 border-white dark:border-surface-900 bg-surface-100 dark:bg-surface-800 flex items-center justify-center text-[10px] font-black text-surface-400">VISA</div>
                    <div class="w-10 h-10 rounded-full border-2 border-white dark:border-surface-900 bg-surface-100 dark:bg-surface-800 flex items-center justify-center text-[10px] font-black text-surface-400">MC</div>
                    <div class="w-10 h-10 rounded-full border-2 border-white dark:border-surface-900 bg-surface-100 dark:bg-surface-800 flex items-center justify-center text-[10px] font-black text-surface-400">AE</div>
                </div>
                <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest flex items-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    SSL PROTECTED
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('storefront.checkout.place', $store) }}">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
                {{-- Customer Info --}}
                <div class="lg:col-span-7 space-y-8">
                    <div class="bg-white dark:bg-surface-800/40 backdrop-blur-md rounded-[2.5rem] border border-surface-100 dark:border-surface-700/50 p-8 sm:p-10 shadow-2xl shadow-black/5 animate-slide-up" style="animation-delay: 0.2s">
                        <h2 class="text-[12px] font-black text-primary-500 uppercase tracking-[0.2em] mb-8 flex items-center gap-3">
                            <span class="w-8 h-[1px] bg-primary-500/30"></span>
                            Deployment Logistics
                        </h2>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="sm:col-span-2 group">
                                <label class="block text-[10px] font-black text-surface-400 uppercase tracking-widest mb-2 ml-1 group-focus-within:text-primary-500 transition-colors">Full Designation *</label>
                                <input type="text" name="customer_name" required value="{{ old('customer_name', auth()->user()?->name) }}" class="w-full px-5 py-4 bg-surface-50 dark:bg-surface-800/50 border-0 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-primary-500/10 dark:text-white transition-all placeholder:text-surface-400" placeholder="John Doe">
                                @error('customer_name') <p class="text-rose-500 text-[10px] font-bold mt-2 ml-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="group">
                                <label class="block text-[10px] font-black text-surface-400 uppercase tracking-widest mb-2 ml-1 group-focus-within:text-primary-500 transition-colors">Communication Node *</label>
                                <input type="email" name="customer_email" required value="{{ old('customer_email', auth()->user()?->email) }}" class="w-full px-5 py-4 bg-surface-50 dark:bg-surface-800/50 border-0 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-primary-500/10 dark:text-white transition-all" placeholder="name@domain.com">
                            </div>

                            <div class="group">
                                <label class="block text-[10px] font-black text-surface-400 uppercase tracking-widest mb-2 ml-1 group-focus-within:text-primary-500 transition-colors">Voice Link</label>
                                <input type="tel" name="phone" value="{{ old('phone') }}" class="w-full px-5 py-4 bg-surface-50 dark:bg-surface-800/50 border-0 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-primary-500/10 dark:text-white transition-all" placeholder="+1 (555) 000-0000">
                            </div>

                            <div class="sm:col-span-2 group">
                                <label class="block text-[10px] font-black text-surface-400 uppercase tracking-widest mb-2 ml-1 group-focus-within:text-primary-500 transition-colors">Physical Coordinates *</label>
                                <textarea name="address" required rows="3" class="w-full px-5 py-4 bg-surface-50 dark:bg-surface-800/50 border-0 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-primary-500/10 dark:text-white transition-all resize-none" placeholder="Street address, City, Logic State, Zip">{{ old('address') }}</textarea>
                            </div>

                            <div class="sm:col-span-2 group">
                                <label class="block text-[10px] font-black text-surface-400 uppercase tracking-widest mb-2 ml-1 group-focus-within:text-primary-500 transition-colors">Architectural Notes</label>
                                <textarea name="notes" rows="2" class="w-full px-5 py-4 bg-surface-50 dark:bg-surface-800/50 border-0 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-primary-500/10 dark:text-white transition-all resize-none" placeholder="Special deployment instructions...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Order Summary --}}
                <div class="lg:col-span-5">
                    <div class="bg-surface-900 border border-white/5 rounded-[2.5rem] p-8 sm:p-10 sticky top-24 shadow-[0_20px_50px_rgba(0,0,0,0.3)] animate-slide-up text-white" style="animation-delay: 0.4s">
                        <h2 class="text-[12px] font-black text-primary-400 uppercase tracking-[0.2em] mb-10 flex items-center gap-3">
                            <span class="w-8 h-[1px] bg-primary-400/30"></span>
                            Registry Summary
                        </h2>

                        <div class="space-y-6 mb-10 max-h-[40vh] overflow-y-auto pr-2 custom-scrollbar">
                            @foreach($items as $item)
                            <div class="flex items-start justify-between group/item">
                                <div class="flex-1">
                                    <p class="text-sm font-black text-white group-hover:text-primary-400 transition-colors cursor-default">{{ $item['product']->name }}</p>
                                    <p class="text-[10px] text-surface-500 font-bold uppercase tracking-widest mt-1">QUANTITY: {{ $item['quantity'] }}</p>
                                </div>
                                <span class="text-sm font-black text-white ml-6">${{ number_format($item['total'], 2) }}</span>
                            </div>
                            @endforeach
                        </div>

                        <div class="space-y-4 border-t border-white/10 pt-8">
                            <div class="flex justify-between text-[11px] font-bold uppercase tracking-widest">
                                <span class="text-surface-400">Registry Subtotal</span>
                                <span class="text-white">${{ number_format($total, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-[11px] font-bold uppercase tracking-widest">
                                <span class="text-surface-400">Node Transmission</span>
                                <span class="text-emerald-400">OPTIMIZED (FREE)</span>
                            </div>
                            
                            <div class="pt-6 mt-6 border-t border-white/10 flex justify-between items-end">
                                <div>
                                    <p class="text-[10px] font-black text-surface-500 uppercase tracking-widest mb-1">Total Payload</p>
                                    <p class="text-4xl font-display font-black text-white leading-none tracking-tighter">${{ number_format($total, 2) }}</p>
                                </div>
                                <div class="bg-primary-500/10 px-3 py-1.5 rounded-lg border border-primary-500/20">
                                    <span class="text-[9px] font-black text-primary-400 uppercase tracking-widest">PAY ON DEPLOY</span>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="w-full h-16 mt-10 bg-primary-600 hover:bg-primary-500 text-white font-display font-black rounded-2xl shadow-2xl shadow-primary-500/20 active:scale-95 transition-all flex items-center justify-center gap-4 text-lg uppercase tracking-[0.2em]">
                            EXECUTE ORDER
                            <svg class="w-6 h-6 animate-bounce-horizontal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </button>
                        
                        <p class="text-[9px] text-center text-surface-500 font-bold mt-6 uppercase tracking-widest leading-relaxed">
                            By executing, you agree to our <a href="#" class="text-surface-400 hover:text-white underline">Protocols</a> and <a href="#" class="text-surface-400 hover:text-white underline">Synchronization Policy</a>.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layouts.storefront>
