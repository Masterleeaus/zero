<x-layouts.admin title="Product Registry">

    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        {{-- Top Bar --}}
        <div class="flex flex-wrap items-center justify-between gap-6">
            <div>
                <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight leading-none mb-2">Product Registry</h1>
                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">Manage Stock, Pricing & Variations</p>
            </div>
            <a href="{{ route('admin.products.create') }}" class="px-6 py-3 bg-blue-600 text-[10px] font-black text-white rounded-2xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 dark:shadow-none uppercase tracking-[0.2em] inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                New Entry
            </a>
        </div>

        {{-- Product Analytics --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Stock Health (Doughnut) --}}
            <div class="bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm flex flex-col justify-between group hover:shadow-xl transition-all duration-500">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Stock Health</h3>
                <div class="h-44 flex items-center justify-center relative">
                    <canvas id="stockHealthChart" 
                            data-values='{!! json_encode([$stockStats['in_stock'] ?? 0, $stockStats['low_stock'] ?? 0, $stockStats['out_of_stock'] ?? 0]) !!}'></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-2xl font-black text-slate-900 dark:text-white leading-none">{{ array_sum($stockStats) }}</span>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Units</span>
                    </div>
                </div>
                <div class="mt-8 grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 leading-none">Healthy</p>
                        <p class="text-sm font-black text-emerald-600 dark:text-emerald-500 leading-none">{{ $stockStats['in_stock'] }}</p>
                    </div>
                    <div class="text-center border-x border-slate-100 dark:border-slate-800 px-2">
                        <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 leading-none">Low</p>
                        <p class="text-sm font-black text-amber-600 dark:text-amber-500 leading-none">{{ $stockStats['low_stock'] }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 leading-none">Depleted</p>
                        <p class="text-sm font-black text-rose-600 dark:text-rose-500 leading-none">{{ $stockStats['out_of_stock'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Top Performing Products (Bar) --}}
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm relative overflow-hidden flex flex-col justify-between group hover:shadow-xl transition-all duration-500">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
                    <div>
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] leading-none mb-1">Velocity stream</h3>
                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Top Selling Variants</p>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-1 bg-blue-50 dark:bg-blue-900/20 rounded-full border border-blue-100 dark:border-blue-800/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                        <span class="text-[9px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest leading-none">Market Flow</span>
                    </div>
                </div>
                <div class="h-44">
                    <canvas id="productVelocityChart"
                            data-labels='{!! json_encode($topProducts->pluck('name')->map(fn($n) => strlen($n) > 15 ? substr($n, 0, 12).'...' : $n)) !!}'
                            data-values='{!! json_encode($topProducts->pluck('order_items_count')) !!}'></canvas>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 p-6 shadow-sm">
            <form method="GET" class="flex flex-col lg:flex-row items-center gap-4">
                <div class="relative flex-1 w-full">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="SEARCH REGISTRY..." class="w-full bg-slate-50/50 dark:bg-slate-800/50 border-slate-200/60 dark:border-slate-700 rounded-full pl-11 pr-6 py-3 text-[10px] font-black uppercase tracking-widest transition-all focus:ring-4 focus:ring-blue-500/10 outline-none text-slate-900 dark:text-white placeholder-slate-400">
                </div>
                <select name="status" class="w-full lg:w-48 bg-slate-50/50 dark:bg-slate-800/50 border-slate-200/60 dark:border-slate-700 rounded-full px-6 py-3 text-[10px] font-black uppercase tracking-widest transition-all focus:ring-4 focus:ring-blue-500/10 text-slate-600 dark:text-slate-400">
                    <option value="">ALL STATUS</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>ACTIVE</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>DRAFT</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>ARCHIVED</option>
                </select>
                <select name="category_id" class="w-full lg:w-48 bg-slate-50/50 dark:bg-slate-800/50 border-slate-200/60 dark:border-slate-700 rounded-full px-6 py-3 text-[10px] font-black uppercase tracking-widest transition-all focus:ring-4 focus:ring-blue-500/10 text-slate-600 dark:text-slate-400">
                    <option value="">ALL CATEGORIES</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ strtoupper($category->name) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full lg:w-auto px-10 py-3 bg-slate-900 dark:bg-white text-[10px] font-black text-white dark:text-slate-900 rounded-full hover:bg-slate-800 dark:hover:bg-slate-100 transition-all uppercase tracking-[0.2em] whitespace-nowrap">
                    Synchronize
                </button>
            </form>
        </div>

        {{-- Products Table --}}
        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200/60 dark:border-slate-800 overflow-hidden shadow-sm group hover:shadow-xl transition-all duration-500">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/30 dark:bg-slate-800/30 text-left border-b border-slate-100 dark:border-slate-800">
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Asset</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Group</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Value</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Volume</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Stage</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Control</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                        @forelse($products as $product)
                        <tr class="group/row hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all duration-300 border-none">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 overflow-hidden flex-shrink-0 border border-slate-200 dark:border-slate-700 shadow-sm group-hover/row:scale-110 transition-transform">
                                        @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="" class="w-full h-full object-cover">
                                        @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        </div>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight leading-none mb-1">{{ $product->name }}</p>
                                        @if($product->sku)<p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest italic">{{ $product->sku }}</p>@endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ $product->category?->name ?? '—' }}</span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <p class="text-sm font-black text-slate-900 dark:text-white leading-none mb-1">${{ number_format($product->price, 2) }}</p>
                                @if($product->hasDiscount())
                                <p class="text-[9px] font-bold text-rose-500 line-through tracking-widest pt-1">${{ number_format($product->compare_price, 2) }}</p>
                                @endif
                            </td>
                            <td class="px-8 py-5 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-sm font-black {{ $product->stock > 10 ? 'text-slate-900 dark:text-white' : ($product->stock > 0 ? 'text-amber-500' : 'text-rose-500') }} leading-none">
                                        {{ $product->stock }}
                                    </span>
                                    <div class="w-8 h-1 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                        <div class="h-full {{ $product->stock > 10 ? 'bg-emerald-500' : ($product->stock > 0 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ min(100, $product->stock) }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-center">
                                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-[0.15em] {{ $product->status === 'active' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/30' : ($product->status === 'draft' ? 'bg-amber-50 text-amber-600 border border-amber-100 dark:bg-amber-900/20 dark:text-amber-500 dark:border-amber-800/30' : 'bg-slate-100 text-slate-500 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700') }}">
                                    {{ $product->status }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="p-2.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-slate-800 rounded-xl transition-all" title="Modify Context">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Terminate this product entry?')" class="inline-block">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-xl transition-all" title="Purge Record">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-8 py-24 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800/50 rounded-full flex items-center justify-center mb-6">
                                        <svg class="w-10 h-10 text-slate-200 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-4">No assets found in registry</p>
                                    <a href="{{ route('admin.products.create') }}" class="text-[9px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest hover:underline">Initialize first entry →</a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($products->hasPages())
            <div class="px-8 py-6 border-t border-slate-50 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-800/30">
                {{ $products->links() }}
            </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Stock Health Chart
            const stockCanvas = document.getElementById('stockHealthChart');
            if (stockCanvas) {
                const stockCtx = stockCanvas.getContext('2d');
                new Chart(stockCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['In Stock', 'Low Stock', 'Out of Stock'],
                        datasets: [{
                            data: JSON.parse(stockCanvas.dataset.values),
                            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                            borderWidth: 0,
                            hoverOffset: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '85%',
                        plugins: { legend: { display: false } }
                    }
                });
            }

            // Product Velocity Chart
            const velocityCanvas = document.getElementById('productVelocityChart');
            if (velocityCanvas) {
                const velocityCtx = velocityCanvas.getContext('2d');
                new Chart(velocityCtx, {
                    type: 'bar',
                    data: {
                        labels: JSON.parse(velocityCanvas.dataset.labels),
                        datasets: [{
                            label: 'Orders',
                            data: JSON.parse(velocityCanvas.dataset.values),
                            backgroundColor: '#3b82f6',
                            borderRadius: 12,
                            barThickness: 32,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(226, 232, 240, 0.4)', drawBorder: false },
                                ticks: { 
                                    color: '#94a3b8',
                                    font: { family: 'Inter', size: 10, weight: 'bold' },
                                    stepSize: 1
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { 
                                    color: '#94a3b8',
                                    font: { family: 'Inter', size: 10, weight: 'bold' }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-layouts.admin>
