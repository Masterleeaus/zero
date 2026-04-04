<x-layouts.admin title="Inventory Control">

    <div class="space-y-10 animate-in fade-in slide-in-from-bottom-6 duration-1000">
        
        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl md:text-5xl font-black text-slate-900 dark:text-white uppercase tracking-tighter leading-none mb-3 italic">Inventory Control</h1>
                <p class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.3em] flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    Warehouse Logistics & Stock Forensic Center
                </p>
            </div>
            
            <div class="flex items-center gap-4">
                <form action="{{ route('admin.inventory.reorder.auto') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-8 py-4 bg-amber-500 text-white text-[10px] font-black uppercase tracking-[0.3em] rounded-2xl shadow-xl shadow-amber-500/20 hover:bg-amber-600 transition-all flex items-center gap-3 disabled:opacity-50 disabled:grayscale" {{ $lowStockProducts->count() == 0 ? 'disabled' : '' }}>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Auto-Replenish Nodes
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            
            {{-- Warehouse Zones --}}
            <div class="xl:col-span-1 space-y-8">
                <div class="bg-white dark:bg-slate-900 p-10 rounded-[2.5rem] border border-slate-200/60 dark:border-slate-800 shadow-sm transition-all hover:shadow-2xl">
                    <div class="flex items-center gap-3 mb-10">
                        <div class="w-1.5 h-6 bg-blue-600 rounded-full"></div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight italic">Operational Zones</h3>
                    </div>
                    
                    <div class="space-y-4 mb-10">
                        @forelse($zones as $zone)
                            <div class="group flex items-center justify-between p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20 hover:border-blue-500/30 transition-all cursor-pointer">
                                <div class="flex items-center gap-5">
                                    <div class="w-12 h-12 rounded-xl bg-white dark:bg-slate-800 text-slate-400 group-hover:bg-blue-600 group-hover:text-white flex items-center justify-center transition-all shadow-inner border border-slate-100 dark:border-slate-700">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black text-slate-900 dark:text-white group-hover:text-blue-600 transition-colors uppercase leading-none mb-1.5">{{ $zone->name }}</h4>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic leading-none">{{ $zone->type }} &bull; {{ $zone->inventories_count }} Units</p>
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-slate-300 group-hover:text-blue-500 group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" /></svg>
                            </div>
                        @empty
                            <div class="text-center py-10 text-slate-400 dark:text-slate-500 text-[10px] font-black uppercase tracking-widest">Zone Matrix Offline</div>
                        @endforelse
                    </div>

                    <form action="{{ route('admin.inventory.zone.store') }}" method="POST" class="space-y-5 border-t border-slate-50 dark:border-white/5 pt-10">
                        @csrf
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-2 leading-none">Register New Coordinate</p>
                        <input type="text" name="name" required placeholder="Zone Label (e.g. A1-Rack)" class="w-full h-14 px-6 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-xs font-black text-slate-900 dark:text-white placeholder-slate-300 focus:ring-4 focus:ring-blue-500/10 transition-all">
                        <div class="grid grid-cols-2 gap-4">
                            <select name="type" required class="w-full h-14 px-6 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 transition-all cursor-pointer appearance-none">
                                <option value="storage">Long-Term Storage</option>
                                <option value="receiving">Entry Receiver</option>
                                <option value="picking">Active Picking</option>
                            </select>
                            <input type="number" name="capacity" placeholder="Capacity" class="w-full h-14 px-6 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-xs font-black text-slate-900 dark:text-white placeholder-slate-300 focus:ring-4 focus:ring-blue-500/10 transition-all">
                        </div>
                        <button type="submit" class="w-full h-14 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl transition-all shadow-xl hover:scale-[1.02]">
                            Initialize Zone Protocol
                        </button>
                    </form>
                </div>
            </div>

            {{-- Reorder Points --}}
            <div class="xl:col-span-2 space-y-8">
                <div class="bg-white dark:bg-slate-900 p-12 rounded-[3.5rem] border border-slate-200/60 dark:border-slate-800 shadow-sm relative overflow-hidden transition-all hover:shadow-2xl">
                    <div class="absolute top-0 right-0 w-80 h-80 bg-amber-500/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>

                    <div class="flex flex-wrap items-center justify-between gap-6 mb-12 relative z-10">
                        <div>
                            <div class="flex items-center gap-3 mb-3">
                                <span class="relative flex h-3 w-3">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                                </span>
                                <h3 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight italic">Low Friction Nodes</h3>
                            </div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] italic">Telemetry alerts for stock depletion</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto relative z-10 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-inner">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 dark:bg-slate-950/20 border-b border-slate-100 dark:border-white/5">
                                    <th class="px-10 py-6 text-[9px] font-black text-slate-400 uppercase tracking-[0.3em]">Product Asset</th>
                                    <th class="px-10 py-6 text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] text-center">Alert Qty</th>
                                    <th class="px-10 py-6 text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] text-center">Current</th>
                                    <th class="px-10 py-6 text-[9px] font-black text-slate-400 uppercase tracking-[0.3em]">Signal</th>
                                    <th class="px-10 py-6 text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-white/5">
                                @forelse($lowStockProducts as $product)
                                <tr class="group hover:bg-amber-600/5 dark:hover:bg-amber-400/5 transition-all duration-300">
                                    <td class="px-10 py-8">
                                        <div class="flex items-center gap-6">
                                            @if($product->image)
                                                <img src="{{ Storage::url($product->image) }}" class="w-14 h-14 rounded-2xl shrink-0 object-cover border border-slate-100 dark:border-slate-800 shadow-sm group-hover:scale-110 transition-transform">
                                            @else
                                                <div class="w-14 h-14 rounded-2xl bg-white dark:bg-slate-800 flex items-center justify-center text-slate-300 dark:text-slate-600 border border-slate-100 dark:border-slate-700 shadow-inner group-hover:text-amber-500 transition-colors">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                                </div>
                                            @endif
                                            <div>
                                                <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase leading-none mb-1.5">{{ $product->name }}</h4>
                                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic">SKU: {{ $product->sku ?? 'NULL' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-10 py-8 text-center text-xs font-black text-slate-400">
                                        {{ $product->alert_quantity }}
                                    </td>
                                    <td class="px-10 py-8 text-center">
                                        <span class="text-lg font-display font-black {{ $product->stock == 0 ? 'text-rose-500' : 'text-amber-500' }}">
                                            {{ $product->stock }}
                                        </span>
                                    </td>
                                    <td class="px-10 py-8">
                                        @if($product->stock == 0)
                                            <span class="inline-flex px-3 py-1 rounded-full text-[8px] font-black uppercase tracking-widest bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-100 dark:border-rose-800/20 shadow-sm shadow-rose-500/10">Null Deletion</span>
                                        @else
                                            <span class="inline-flex px-3 py-1 rounded-full text-[8px] font-black uppercase tracking-widest bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-100 dark:border-amber-800/20">Depleted</span>
                                        @endif
                                    </td>
                                    <td class="px-10 py-8 text-right">
                                        <a href="{{ route('admin.purchases.create', ['product_id' => $product->id]) }}" class="inline-flex px-5 py-2.5 bg-blue-600 text-white text-[9px] font-black uppercase tracking-widest rounded-xl shadow-lg shadow-blue-500/20 hover:bg-blue-700 transition-all hover:scale-[1.05]">Replenish</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-10 py-32 text-center">
                                        <div class="w-20 h-20 mx-auto bg-slate-50 dark:bg-slate-800/50 rounded-[2.5rem] flex items-center justify-center text-emerald-500 mb-8 border border-slate-100 dark:border-slate-800 shadow-inner">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight italic mb-2">Inventory Optimized</h3>
                                        <p class="text-slate-400 dark:text-slate-500 text-[10px] font-black uppercase tracking-[0.3em]">All nodes reporting normal stock coefficients</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                {{-- Ancillary Controls --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                     <a href="{{ route('admin.inventory-transfers.index') }}" class="group bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-200/60 dark:border-slate-800 shadow-sm transition-all hover:shadow-2xl hover:border-blue-500/30">
                         <div class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-slate-800 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-10 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-inner border border-blue-100 dark:border-slate-700">
                             <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                         </div>
                         <h4 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight leading-none group-hover:text-blue-600 transition-colors italic">Internal Mobility</h4>
                         <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-4 italic leading-relaxed">Manage unit movement between ecosystem nodes and zones.</p>
                     </a>

                     <div class="group bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-200/60 dark:border-slate-800 shadow-sm transition-all hover:shadow-2xl hover:border-indigo-500/30 cursor-pointer">
                         <div class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-10 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-inner border border-indigo-100 dark:border-slate-700">
                             <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                         </div>
                         <h4 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight leading-none group-hover:text-indigo-600 transition-colors italic">Asset Valuation</h4>
                         <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-4 italic leading-relaxed">Financial audit of current warehouse liquidity coefficients.</p>
                     </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
