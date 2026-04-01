<section class="py-24 px-6 bg-surface-50 dark:bg-surface-900/50">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row items-end justify-between mb-16 gap-6">
            <div class="max-w-xl">
                <span class="text-primary-500 text-[10px] font-black uppercase tracking-[0.3em] mb-4 block">Curated Selection</span>
                <h2 class="text-4xl lg:text-5xl font-display font-black text-surface-900 dark:text-white tracking-tighter">
                    {{ $section->getContent('title', 'Featured Collections') }}
                </h2>
                <p class="mt-4 text-surface-500">
                    {{ $section->getContent('subtitle', 'Discovery the latest architectural pieces for your digital workspace.') }}
                </p>
            </div>
            <a href="#" class="group flex items-center gap-3 text-sm font-black text-primary-500 uppercase tracking-widest hover:text-primary-400 transition-colors">
                View All Assets
                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @php
                // Mock products if none available, though in a real app these would come from the DB
                $products = \App\Models\Product::where('store_id', $store->id)->limit(4)->get();
            @endphp

            @forelse($products as $product)
            <div class="group">
                <div class="relative aspect-[4/5] bg-white dark:bg-surface-800 rounded-[2.5rem] overflow-hidden border border-surface-200 dark:border-surface-700 shadow-sm transition-all duration-500 group-hover:shadow-2xl group-hover:shadow-primary-500/10 group-hover:-translate-y-2">
                    @if($product->image)
                        <img src="{{ Storage::url($product->image) }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" alt="{{ $product->name }}">
                    @else
                        <div class="w-full h-full bg-surface-100 dark:bg-surface-700 flex items-center justify-center">
                            <svg class="w-12 h-12 text-surface-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                    
                    {{-- Quick Action Overlay --}}
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3 backdrop-blur-sm">
                        <button class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-surface-900 hover:bg-primary-500 hover:text-white transition-all transform translate-y-4 group-hover:translate-y-0 duration-500 shadow-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </button>
                    </div>
                </div>
                <div class="mt-6 space-y-1 px-2">
                    <h3 class="text-lg font-bold text-surface-900 dark:text-white group-hover:text-primary-500 transition-colors">{{ $product->name }}</h3>
                    <div class="flex items-center justify-between">
                        <p class="text-surface-500 text-sm font-medium">{{ $product->category->name ?? 'Uncategorized' }}</p>
                        <p class="text-primary-500 font-black tracking-tight">${{ number_format($product->price, 2) }}</p>
                    </div>
                </div>
            </div>
            @empty
            @for($i = 1; $i <= 4; $i++)
            <div class="group opacity-40">
                <div class="aspect-[4/5] bg-surface-200 dark:bg-surface-800 rounded-[2.5rem] border-2 border-dashed border-surface-300 dark:border-surface-600 flex items-center justify-center">
                    <span class="text-[10px] font-black uppercase tracking-widest text-surface-400">Inventory Slot {{ $i }}</span>
                </div>
                <div class="mt-4 h-4 bg-surface-200 dark:bg-surface-800 rounded-lg w-3/4"></div>
                <div class="mt-2 h-3 bg-surface-100 dark:bg-surface-900 rounded-lg w-1/2"></div>
            </div>
            @endfor
            @endforelse
        </div>
    </div>
</section>
