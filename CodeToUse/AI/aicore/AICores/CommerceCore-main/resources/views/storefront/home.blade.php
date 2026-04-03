<x-layouts.storefront :store="$store">

    {{-- Builder Sections --}}
    @if($homepage)
        @foreach($homepage->activeSections as $section)
            @include('storefront.sections.' . $section->type, ['section' => $section, 'store' => $store])
        @endforeach
    @else
        {{-- Default Hero --}}
        <section class="relative bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 py-24 sm:py-32">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg%20width%3D%2260%22%20height%3D%2260%22%20viewBox%3D%220%200%2060%2060%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cg%20fill%3D%22none%22%20fill-rule%3D%22evenodd%22%3E%3Cg%20fill%3D%22%23ffffff%22%20fill-opacity%3D%220.05%22%3E%3Cpath%20d%3D%22M36%2034v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6%2034v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6%204V0H4v4H0v2h4v4h2V6h4V4H6z%22%2F%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E')] opacity-40"></div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-display font-extrabold text-white leading-tight">Welcome to {{ $store->name }}</h1>
                <p class="mt-6 text-xl text-primary-200 max-w-2xl mx-auto">{{ $store->description ?? 'Discover amazing products at great prices' }}</p>
                <div class="mt-10">
                    <a href="{{ route('storefront.products', $store->slug) }}" class="inline-flex items-center gap-2 px-8 py-4 bg-white text-primary-700 font-display font-bold rounded-2xl shadow-2xl shadow-primary-900/30 hover:shadow-primary-900/50 hover:-translate-y-0.5 transition-all">
                        Shop Now
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- Featured Products --}}
    @if($featuredProducts->count())
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-display font-bold text-surface-800 dark:text-white">Featured Products</h2>
            <p class="mt-3 text-surface-500 max-w-xl mx-auto">Handpicked items just for you</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
            @foreach($featuredProducts as $product)
            <a href="{{ route('storefront.product.detail', [$store->slug, $product->slug]) }}" class="group card-hover bg-white dark:bg-surface-800 rounded-2xl border border-surface-200 dark:border-surface-700 overflow-hidden">
                <div class="aspect-square bg-surface-100 dark:bg-surface-700 overflow-hidden relative">
                    @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                    <div class="w-full h-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-surface-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    @endif
                    @if($product->hasDiscount())
                    <span class="absolute top-3 left-3 px-2.5 py-1 bg-red-500 text-white text-xs font-bold rounded-lg">-{{ $product->discountPercentage() }}%</span>
                    @endif
                </div>
                <div class="p-4">
                    <h3 class="text-sm font-semibold text-surface-800 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors line-clamp-2">{{ $product->name }}</h3>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="text-lg font-display font-bold text-primary-600 dark:text-primary-400">@money($product->price)</span>
                        @if($product->hasDiscount())
                        <span class="text-sm text-surface-400 line-through">@money($product->compare_price)</span>
                        @endif
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        <div class="text-center mt-10">
            <a href="{{ route('storefront.products', $store->slug) }}" class="inline-flex items-center gap-2 px-6 py-3 border-2 border-primary-500 text-primary-600 dark:text-primary-400 font-semibold rounded-xl hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                View All Products
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </section>
    @endif

    {{-- Categories --}}
    @if($categories->count())
    <section class="bg-surface-50 dark:bg-surface-800/50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-display font-bold text-surface-800 dark:text-white text-center mb-10">Shop by Category</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach($categories as $category)
                <a href="{{ route('storefront.products', [$store->slug, 'category' => $category->id]) }}" class="card-hover bg-white dark:bg-surface-800 rounded-2xl p-6 text-center border border-surface-200 dark:border-surface-700">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/30 dark:to-primary-800/30 flex items-center justify-center mb-3">
                        <svg class="w-7 h-7 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z"/></svg>
                    </div>
                    <span class="text-sm font-semibold text-surface-700 dark:text-surface-300">{{ $category->name }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif
</x-layouts.storefront>
