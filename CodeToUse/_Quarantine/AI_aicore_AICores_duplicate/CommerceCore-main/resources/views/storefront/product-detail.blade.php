<x-layouts.storefront :store="$store">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-12 animate-fade-in">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-16">
            {{-- Product Gallery --}}
            <div class="lg:col-span-7">
                <div class="sticky top-24 space-y-4">
                    <div class="aspect-[4/5] bg-surface-50 dark:bg-surface-800 rounded-[2.5rem] overflow-hidden border border-surface-100 dark:border-surface-700/50 shadow-2xl shadow-black/5 relative group/gallery">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover/gallery:opacity-100 transition-opacity duration-700"></div>
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover/gallery:scale-105 transition-transform duration-[2s] ease-out">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center text-surface-300 gap-4">
                                <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <span class="text-xs font-black uppercase tracking-widest">No Image Deployed</span>
                            </div>
                        @endif

                        {{-- Floating Badge --}}
                        @if($product->hasDiscount())
                        <div class="absolute top-8 left-8">
                            <span class="px-5 py-2 bg-rose-500 text-white text-[10px] font-black rounded-full shadow-xl shadow-rose-500/40 uppercase tracking-widest">
                                Save {{ $product->discountPercentage() }}%
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Product Info --}}
            <div class="lg:col-span-5 flex flex-col pt-4">
                <nav class="flex items-center gap-2 mb-8 animate-slide-up" style="animation-delay: 0.1s">
                    <a href="{{ route('storefront.home', $store->slug) }}" class="text-[10px] font-black text-surface-400 uppercase tracking-widest hover:text-primary-500 transition-colors">Home</a>
                    <svg class="w-2.5 h-2.5 text-surface-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    @if($product->category)
                        <a href="{{ route('storefront.products', [$store->slug, 'category' => $product->category_id]) }}" class="text-[10px] font-black text-primary-500 uppercase tracking-widest">{{ $product->category->name }}</a>
                    @endif
                </nav>

                <h1 class="text-4xl sm:text-5xl font-display font-black text-surface-900 dark:text-white leading-tight mb-4 animate-slide-up" style="animation-delay: 0.2s">
                    {{ $product->name }}
                </h1>

                <div class="flex items-baseline gap-4 mb-8 animate-slide-up" style="animation-delay: 0.3s">
                    <span class="text-4xl font-display font-black text-primary-600 dark:text-primary-400">${{ number_format($product->price, 2) }}</span>
                    @if($product->hasDiscount())
                        <span class="text-xl text-surface-400 line-through font-bold">${{ number_format($product->compare_price, 2) }}</span>
                    @endif
                </div>

                <div class="flex items-center gap-4 mb-10 animate-slide-up" style="animation-delay: 0.4s">
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-surface-50 dark:bg-surface-800/50 rounded-xl border border-surface-100 dark:border-surface-700/50">
                        <span class="w-2 h-2 rounded-full {{ $product->inStock() ? 'bg-emerald-500' : 'bg-rose-500' }} animate-pulse"></span>
                        <span class="text-[10px] font-black {{ $product->inStock() ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} uppercase tracking-widest">
                            {{ $product->inStock() ? $product->stock . ' IN STOCK' : 'DEPLOYMENT PENDING' }}
                        </span>
                    </div>
                </div>

                @if($product->description)
                <div class="text-surface-500 dark:text-surface-400 leading-relaxed text-lg mb-10 animate-slide-up" style="animation-delay: 0.5s">
                    {{ $product->description }}
                </div>
                @endif

                @if($product->inStock())
                <form method="POST" action="{{ route('storefront.cart.add', $store->slug) }}" class="space-y-8 animate-slide-up" style="animation-delay: 0.6s">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    
                    @if($product->allow_subscription)
                    <div class="p-8 glass rounded-[2rem] border border-white/10 relative overflow-hidden group/sub">
                        <div class="absolute inset-0 bg-primary-500/5 opacity-0 group-hover/sub:opacity-100 transition-opacity"></div>
                        <h4 class="text-[10px] font-black text-primary-500 uppercase tracking-widest mb-6 flex items-center gap-3 italic">
                            🚀 Multi-Delivery Subscription
                        </h4>
                        <div class="grid grid-cols-1 gap-4">
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="purchase_type" value="onetime" checked class="peer sr-only">
                                <div class="p-5 bg-white dark:bg-surface-800 rounded-2xl border border-surface-100 dark:border-surface-700 peer-checked:border-primary-500 peer-checked:ring-4 peer-checked:ring-primary-500/10 transition-all flex items-center justify-between">
                                    <div>
                                        <p class="text-[10px] font-black text-surface-400 uppercase tracking-widest">Standard Deployment</p>
                                        <p class="text-lg font-black text-surface-900 dark:text-white mt-1">One-time Purchase</p>
                                    </div>
                                    <p class="text-xl font-display font-black text-surface-900 dark:text-white">${{ number_format($product->price, 2) }}</p>
                                </div>
                            </label>
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="purchase_type" value="subscription" class="peer sr-only">
                                <div class="p-5 bg-white dark:bg-surface-800 rounded-2xl border border-surface-100 dark:border-surface-700 peer-checked:border-primary-500 peer-checked:ring-4 peer-checked:ring-primary-500/10 transition-all flex items-center justify-between">
                                    <div>
                                        <div class="flex items-center gap-3">
                                            <p class="text-[10px] font-black text-primary-500 uppercase tracking-widest">Architects Choice</p>
                                            <span class="px-2 py-0.5 bg-primary-500 text-white text-[8px] font-black rounded uppercase">-{{ (int)$product->subscription_discount_percentage }}%</span>
                                        </div>
                                        <p class="text-lg font-black text-surface-900 dark:text-white mt-1">Subscribe & Save</p>
                                        <p class="text-[10px] text-surface-400 font-bold mt-1 uppercase tracking-widest">Delivers every {{ $product->subscription_interval }}</p>
                                    </div>
                                    <p class="text-xl font-display font-black text-primary-500">${{ number_format($product->price * (1 - $product->subscription_discount_percentage/100), 2) }}</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    @endif

                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <div class="flex items-center bg-surface-50 dark:bg-surface-800 rounded-2xl p-1 border border-surface-100 dark:border-surface-700 w-full sm:w-auto">
                            <button type="button" onclick="this.nextElementSibling.stepDown()" class="w-12 h-12 flex items-center justify-center text-surface-400 hover:text-primary-500 transition-colors">−</button>
                            <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" class="w-16 text-center border-0 bg-transparent text-sm font-black dark:text-white focus:ring-0">
                            <button type="button" onclick="this.previousElementSibling.stepUp()" class="w-12 h-12 flex items-center justify-center text-surface-400 hover:text-primary-500 transition-colors">+</button>
                        </div>
                        <button type="submit" class="w-full h-14 bg-surface-900 dark:bg-primary-600 hover:bg-black dark:hover:bg-primary-500 text-white font-display font-black rounded-2xl shadow-2xl shadow-primary-500/20 active:scale-95 transition-all flex items-center justify-center gap-3 text-lg uppercase tracking-widest">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            Add to Registry
                        </button>
                    </div>
                </form>
                @endif

                {{-- Branch Availability --}}
                @if($store->branches->count() > 1)
                <div class="mt-12 pt-10 border-t border-surface-100 dark:border-surface-800/50 animate-slide-up" style="animation-delay: 0.7s">
                    <h4 class="text-[10px] font-black text-surface-400 uppercase tracking-widest mb-6 flex items-center gap-3 italic">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                        Nodal Availability
                    </h4>
                    <div class="grid grid-cols-1 gap-3">
                        @foreach($store->branches as $branch)
                        @php $inventory = $branch->inventories()->where('product_id', $product->id)->first(); @endphp
                        <div class="flex justify-between items-center p-5 bg-white dark:bg-surface-800/40 rounded-3xl border border-surface-100 dark:border-surface-700/50 hover:border-primary-500/30 transition-all group/branch">
                            <div>
                                <p class="text-[11px] font-black text-surface-900 dark:text-white uppercase tracking-tighter group-hover/branch:text-primary-500 transition-colors">{{ $branch->name }}</p>
                                <p class="text-[9px] text-surface-400 font-bold uppercase tracking-widest mt-0.5">{{ \Illuminate\Support\Str::limit($branch->address, 40) }}</p>
                            </div>
                            <span class="px-4 py-1.5 text-[9px] font-black rounded-xl uppercase tracking-widest {{ ($inventory->stock ?? 0) > 0 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400' }}">
                                {{ ($inventory->stock ?? 0) > 0 ? 'READY' : 'NODE OFFLINE' }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Reviews Section --}}
        <div class="mt-32 animate-slide-up" style="animation-delay: 0.8s">
            @include('storefront.partials.reviews', ['product' => $product, 'storeSlug' => $store->slug])
        </div>

        {{-- Related Products --}}
        @if($relatedProducts->count())
        <div class="mt-32 border-t border-surface-100 dark:border-surface-800/50 pt-20 animate-slide-up" style="animation-delay: 0.9s">
            <div class="flex items-center justify-between mb-12">
                <h2 class="text-3xl font-display font-black text-surface-900 dark:text-white tracking-tight">Synchronized Items</h2>
                <a href="{{ route('storefront.products', $store->slug) }}" class="text-[10px] font-black text-primary-500 uppercase tracking-widest hover:translate-x-1 transition-transform inline-flex items-center gap-2">Explore All <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-8">
                @foreach($relatedProducts as $related)
                <a href="{{ route('storefront.product.detail', ['store' => $store->slug, 'product' => $related->slug]) }}" class="group relative">
                    <div class="aspect-square bg-surface-50 dark:bg-surface-800 rounded-[2rem] overflow-hidden border border-surface-100 dark:border-surface-700/50 mb-6 relative">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        @if($related->image)
                        <img src="{{ asset('storage/' . $related->image) }}" alt="{{ $related->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        @else
                        <div class="w-full h-full flex items-center justify-center"><svg class="w-10 h-10 text-surface-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
                        @endif
                    </div>
                    <h3 class="text-sm font-bold text-surface-900 dark:text-white line-clamp-1 mb-1 group-hover:text-primary-500 transition-colors">{{ $related->name }}</h3>
                    <span class="text-lg font-display font-black text-surface-900 dark:text-white">${{ number_format($related->price, 2) }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</x-layouts.storefront>
