<x-layouts.storefront :store="$store">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col lg:flex-row gap-8">
            {{-- Filters Sidebar --}}
            <aside class="lg:w-64 flex-shrink-0">
                <div class="bg-white dark:bg-surface-800 rounded-2xl border border-surface-200 dark:border-surface-700 p-5 sticky top-24">
                    <h3 class="text-sm font-semibold text-surface-500 uppercase tracking-wider mb-4">Filters</h3>
                    <form method="GET" action="{{ route('storefront.products', $store->slug) }}" class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Category</label>
                            <select name="category" class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white">
                                <option value="">All</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-medium text-surface-500 mb-1">Min $</label>
                                <input type="number" name="min_price" value="{{ request('min_price') }}" class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-surface-500 mb-1">Max $</label>
                                <input type="number" name="max_price" value="{{ request('max_price') }}" class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Sort</label>
                            <select name="sort" class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white">
                                <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Newest</option>
                                <option value="price" {{ request('sort') === 'price' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Name</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full px-4 py-2.5 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold rounded-xl transition-colors">Apply Filters</button>
                    </form>
                </div>
            </aside>

            {{-- Product Grid --}}
            <div class="flex-1">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-display font-bold text-surface-800 dark:text-white">Products</h1>
                    <span class="text-sm text-surface-400">{{ $products->total() }} results</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-5">
                    @forelse($products as $product)
                    <a href="{{ route('storefront.product.detail', [$store->slug, $product->slug]) }}" class="group card-hover bg-white dark:bg-surface-800 rounded-2xl border border-surface-200 dark:border-surface-700 overflow-hidden">
                        <div class="aspect-square bg-surface-100 dark:bg-surface-700 overflow-hidden relative">
                            @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                            <div class="w-full h-full flex items-center justify-center"><svg class="w-12 h-12 text-surface-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
                            @endif
                            @if($product->hasDiscount())
                            <span class="absolute top-3 left-3 px-2.5 py-1 bg-red-500 text-white text-xs font-bold rounded-lg">-{{ $product->discountPercentage() }}%</span>
                            @endif
                            @if(!$product->inStock())
                            <div class="absolute inset-0 bg-black/50 flex items-center justify-center"><span class="text-white font-bold text-sm">Out of Stock</span></div>
                            @endif
                        </div>
                        <div class="p-4">
                            <h3 class="text-sm font-semibold text-surface-800 dark:text-white group-hover:text-primary-600 transition-colors line-clamp-2">{{ $product->name }}</h3>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-lg font-display font-bold text-primary-600 dark:text-primary-400">${{ number_format($product->price, 2) }}</span>
                                @if($product->hasDiscount())
                                <span class="text-sm text-surface-400 line-through">${{ number_format($product->compare_price, 2) }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                    @empty
                    <div class="col-span-full text-center py-16">
                        <svg class="w-16 h-16 mx-auto text-surface-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <p class="text-surface-500">No products found</p>
                    </div>
                    @endforelse
                </div>
                @if($products->hasPages())<div class="mt-8">{{ $products->withQueryString()->links() }}</div>@endif
            </div>
        </div>
    </div>
</x-layouts.storefront>
