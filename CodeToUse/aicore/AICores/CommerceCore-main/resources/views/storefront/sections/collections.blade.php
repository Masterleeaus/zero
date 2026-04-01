<section class="py-16">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="text-3xl font-black text-surface-900 dark:text-white mb-2">Shop by Category</h2>
                <p class="text-surface-500">Explore our curated collections.</p>
            </div>
            <a href="{{ route('storefront.products', ['store' => session('store_slug')]) }}" class="text-xs font-black text-indigo-600 tracking-widest uppercase border-b-2 border-indigo-600 pb-1">View All</a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            @foreach(\App\Models\Category::where('store_id', $store->id)->limit(4)->get() as $cat)
            <a href="{{ route('storefront.products', ['store' => session('store_slug'), 'category' => $cat->id]) }}" 
               class="group relative h-64 rounded-3xl overflow-hidden bg-surface-100 dark:bg-surface-800">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                <div class="absolute bottom-6 left-6 text-white group-hover:bottom-8 transition-all duration-300">
                    <p class="text-[10px] font-black uppercase tracking-widest mb-1 opacity-80">Collection</p>
                    <h4 class="text-lg font-black">{{ $cat->name }}</h4>
                </div>
                <div class="absolute top-6 right-6 w-8 h-8 rounded-full bg-white/20 backdrop-blur flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
