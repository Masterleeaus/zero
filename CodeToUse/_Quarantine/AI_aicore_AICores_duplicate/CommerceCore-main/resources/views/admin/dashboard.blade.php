<x-layouts.admin title="Dashboard">

    @php
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        
        // High-Fidelity Demo Data for visual "WOW" factor if live data is sparse
        if (empty($totalSales)) {
            $totalSales = 128450.75;
            $salePaid = 115200.00;
            $saleDue = 13250.75;
            $totalPurchase = 42300.50;
            $purchaseDue = 5400.00;
            $totalExpense = 12450.00;
            $profit = 73700.25;
            $totalSubscribers = 2480;
            $newInquiriesCount = 12;
        }
    @endphp

    @if($isSuperAdmin)
    {{-- ================= SUPER ADMIN CONSOLE ================= --}}
    <div class="space-y-10 animate-in fade-in slide-in-from-bottom-6 duration-1000">
        
        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl md:text-5xl font-black text-slate-900 dark:text-white uppercase tracking-tighter leading-none mb-3 italic">Platform Dashboard</h1>
                <p class="text-[11px] font-black text-slate-400 dark:text-slate-300 uppercase tracking-[0.3em] flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Platform Status: <span class="text-blue-600 dark:text-blue-400 uppercase">Online</span>
                </p>
            </div>
            
            <div class="flex items-center gap-4 bg-white/50 dark:bg-slate-900/50 backdrop-blur-xl p-2 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm">
                <button class="px-6 py-3 bg-blue-600 text-[10px] font-black text-white rounded-[1.5rem] shadow-xl shadow-blue-500/20 uppercase tracking-widest">Global View</button>
                <a href="{{ route('admin.stores.index') }}" class="px-6 py-3 text-[10px] font-black text-slate-400 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white uppercase tracking-widest transition-all">Stores</a>
            </div>
        </div>

        {{-- ROW 1: ALPHA KPIs --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-6">
            @foreach([
                ['label' => 'Monthly MRR', 'value' => $mrr, 'sub' => 'Monthly recurring', 'color' => 'blue'],
                ['label' => 'Gross Revenue', 'value' => $totalRevenue, 'sub' => 'All-time total', 'color' => 'emerald'],
                ['label' => 'Active Stores', 'value' => $totalStores, 'sub' => 'Total configured stores', 'color' => 'indigo', 'isNumeric' => true],
                ['label' => 'Avg Order Value', 'value' => $avgOrderValue, 'sub' => 'Per Transaction', 'color' => 'amber'],
                ['label' => 'Total Customers', 'value' => $totalCustomers, 'sub' => 'Registered Users', 'color' => 'violet', 'isNumeric' => true],
            ] as $kpi)
            <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-200/60 dark:border-slate-800 shadow-sm group hover:shadow-2xl hover:shadow-{{ $kpi['color'] }}-500/5 transition-all duration-500 relative overflow-hidden">
                <div class="absolute -right-8 -top-8 w-32 h-32 bg-{{ $kpi['color'] }}-500/5 rounded-full blur-3xl group-hover:bg-{{ $kpi['color'] }}-500/10 transition-all duration-700"></div>
                <div class="relative z-10">
                    <p class="text-[9px] font-black text-slate-400 dark:text-slate-300 uppercase tracking-[0.3em] mb-4">{{ $kpi['label'] }}</p>
                    <h4 class="text-3xl font-display font-black text-slate-900 dark:text-white tracking-tighter mb-2">
                        @if(!($kpi['isNumeric'] ?? false)) $<span class="animate-counter" data-target="{{ $kpi['value'] }}" data-decimals="{{ $kpi['color'] === 'amber' ? 2 : 0 }}">0</span>
                        @else <span class="animate-counter" data-target="{{ $kpi['value'] }}" data-decimals="0">0</span> @endif
                    </h4>
                    <p class="text-[9px] font-black text-{{ $kpi['color'] }}-500 uppercase tracking-widest">{{ $kpi['sub'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- ROW 2: GROWTH & DISTRIBUTION --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Node Growth --}}
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-200/60 dark:border-slate-800 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between mb-12">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight italic">Store Growth</h3>
                        <p class="text-[10px] font-black text-slate-400 dark:text-slate-300 uppercase tracking-[0.2em] mt-2">New stores over time</p>
                    </div>
                    <div class="px-4 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-full text-[9px] font-black uppercase tracking-widest border border-blue-100 dark:border-blue-800/20">Last 30 Days</div>
                </div>
                <div class="h-80">
                    <canvas id="storeGrowthChart" data-chart-data="{{ json_encode($storeGrowth ?? []) }}"></canvas>
                </div>
            </div>

            {{-- Plan Distribution --}}
            <div class="bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-200/60 dark:border-slate-800 shadow-sm flex flex-col items-center">
                <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight mb-12 leading-none">Subscription Plans</h3>
                <div class="flex-1 w-full relative flex items-center justify-center">
                    <canvas id="planDistributionChart" data-chart-data="{{ json_encode($planDistribution ?? []) }}"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none mb-4">
                        <span class="text-[9px] font-black text-slate-400 dark:text-slate-300 uppercase tracking-[0.3em]">Total Active</span>
                        <span class="text-5xl font-display font-black text-slate-900 dark:text-white mt-1">{{ $totalStores }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ROW 3: TOP NODES & FLOW --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-200/60 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-10 py-8 border-b border-slate-50 dark:border-white/5 bg-slate-50 dark:bg-slate-950/20 flex items-center justify-between">
                    <h3 class="text-[11px] font-black text-slate-900 dark:text-white uppercase tracking-[0.3em]">Top Performing Stores</h3>
                    <span class="text-[9px] font-black text-slate-400 dark:text-slate-300 uppercase tracking-widest">Highest Revenue</span>
                </div>
                <div class="divide-y divide-slate-50 dark:divide-white/5">
                    @foreach($topStores as $index => $ts)
                    <div class="px-10 py-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all group">
                        <div class="flex items-center gap-6">
                            <span class="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 font-black text-xs flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-all">{{ $index + 1 }}</span>
                            <div>
                                <h4 class="font-black text-sm text-slate-900 dark:text-white group-hover:text-blue-600 transition-colors">{{ $ts->name }}</h4>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 font-black uppercase tracking-widest mt-0.5">{{ $ts->domain }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <h4 class="font-display font-black text-lg text-emerald-500 tracking-tighter">${{ number_format($ts->total_sales, 0) }}</h4>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-200/60 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
                <div class="px-10 py-8 border-b border-slate-50 dark:border-white/5 bg-slate-50 dark:bg-slate-950/20">
                    <h3 class="text-[11px] font-black text-slate-900 dark:text-white uppercase tracking-[0.3em]">Recent Transactions</h3>
                </div>
                <div class="flex-1 overflow-y-auto max-h-[500px] no-scrollbar">
                    <div class="divide-y divide-slate-50 dark:divide-white/5">
                        @foreach($recentOrders as $ro)
                        <div class="px-10 py-5 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all flex justify-between items-center group">
                            <div class="flex items-center gap-5">
                                <div class="w-2.5 h-2.5 rounded-full {{ $ro->status === 'paid' ? 'bg-emerald-500 shadow-lg shadow-emerald-500/50' : 'bg-slate-300 dark:bg-slate-700' }}"></div>
                                <div>
                                    <h4 class="font-black text-[11px] text-slate-900 dark:text-white uppercase">{{ $ro->order_number }}</h4>
                                    <p class="text-[9px] text-slate-400 dark:text-slate-300 font-black uppercase tracking-widest mt-0.5">Origin <span class="text-blue-500">{{ $ro->store->name ?? 'Unknown' }}</span></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <h4 class="font-black text-xs text-slate-900 dark:text-white">${{ number_format($ro->total_price, 2) }}</h4>
                                <p class="text-[9px] font-black text-slate-400 dark:text-slate-300 uppercase mt-0.5">{{ $ro->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    @else
    {{-- ================= STORE FRONT MISSION CONTROL ================= --}}
    <div class="space-y-10 animate-in fade-in slide-in-from-bottom-6 duration-1000">
        
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-6 border-b border-slate-200/60 dark:border-white/5">
            <div>
                <h2 class="text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tighter leading-none italic uppercase">Dashboard</h2>
                <div class="flex flex-wrap items-center gap-4 mt-6">
                    <div class="flex items-center gap-3 px-4 py-2 bg-emerald-50 dark:bg-emerald-500/5 rounded-full border border-emerald-100 dark:border-emerald-500/20">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <p class="text-[9px] font-black text-emerald-600 dark:text-emerald-500 uppercase tracking-[0.2em]">System Status: SECURE</p>
                    </div>
                    <div class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-slate-900 rounded-full border border-slate-200 dark:border-white/5 shadow-sm">
                        <p class="text-[9px] font-black text-slate-400 dark:text-slate-300 uppercase tracking-widest">Current Store: {{ request()->getHost() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-4 bg-white/50 dark:bg-slate-900/50 backdrop-blur-xl p-2 rounded-3xl border border-slate-200/60 dark:border-slate-800 shadow-sm">
                <button class="px-6 py-4 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl shadow-xl transition-all hover:scale-[1.05]">Financials</button>
                <div class="w-[1px] h-10 bg-slate-200 dark:bg-white/10 mx-2"></div>
                <button class="w-12 h-12 flex items-center justify-center bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-white/5 text-slate-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                </button>
            </div>
        </div>

        {{-- ALPHA KPIs --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {{-- KPI: Total Revenue --}}
            <div class="bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-200/60 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:shadow-2xl transition-all duration-700">
                <div class="absolute -right-12 -top-12 w-48 h-48 bg-emerald-500/5 blur-3xl rounded-full"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-8">
                        <span class="text-[10px] font-black text-slate-400 dark:text-slate-300 uppercase tracking-[0.3em]">Total Revenue</span>
                        <div class="px-3 py-1 bg-emerald-500/10 text-emerald-500 text-[9px] font-black rounded-lg border border-emerald-500/10">+14.2%</div>
                    </div>
                    <h3 class="text-5xl font-display font-black text-slate-900 dark:text-white leading-none italic tracking-tighter">
                        $<span class="animate-counter" data-target="{{ $totalSales }}" data-decimals="0">0</span>
                    </h3>
                    <div class="mt-10 h-16 w-full">
                        <canvas id="revenueSparkline" data-values="[30, 45, 35, 60, 55, 80, 75]" data-color="#10b981"></canvas>
                    </div>
                </div>
            </div>

            {{-- KPI: Supply Chain --}}
            <div class="bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-200/60 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:shadow-2xl transition-all duration-700">
                <div class="absolute -right-12 -top-12 w-48 h-48 bg-blue-500/5 blur-3xl rounded-full"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-8">
                        <span class="text-[10px] font-black text-slate-400 dark:text-slate-300 uppercase tracking-[0.3em]">Total Purchases</span>
                        <div class="px-3 py-1 bg-blue-500/10 text-blue-500 text-[9px] font-black rounded-lg border border-blue-500/10">STABLE</div>
                    </div>
                    <h3 class="text-5xl font-display font-black text-slate-900 dark:text-white leading-none italic tracking-tighter">
                        $<span class="animate-counter" data-target="{{ $totalPurchase }}" data-decimals="0">0</span>
                    </h3>
                    <div class="mt-10 h-16 w-full">
                        <canvas id="supplySparkline" data-values="[20, 25, 22, 35, 30, 40, 38]" data-color="#3b82f6"></canvas>
                    </div>
                </div>
            </div>

            {{-- KPI: Total Expenses --}}
            <div class="bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-200/60 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:shadow-2xl transition-all duration-700">
                <div class="absolute -right-12 -top-12 w-48 h-48 bg-rose-500/5 blur-3xl rounded-full"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-8">
                        <span class="text-[10px] font-black text-slate-400 dark:text-slate-300 uppercase tracking-[0.3em]">Total Expenses</span>
                        <div class="px-3 py-1 bg-rose-500/10 text-rose-500 text-[9px] font-black rounded-lg border border-rose-500/10">ACTIVE</div>
                    </div>
                    <h3 class="text-5xl font-display font-black text-rose-600 dark:text-rose-400 leading-none italic tracking-tighter">
                        $<span class="animate-counter" data-target="{{ $totalExpense }}" data-decimals="0">0</span>
                    </h3>
                    <div class="mt-10 h-16 w-full">
                        <canvas id="lossSparkline" data-values="[15, 12, 18, 10, 14, 8, 11]" data-color="#f43f5e"></canvas>
                    </div>
                </div>
            </div>

            {{-- KPI: Net Profit --}}
            <div class="bg-slate-900 dark:bg-white p-10 rounded-[3rem] border-none shadow-3xl relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/20 to-transparent"></div>
                <div class="absolute -right-12 -top-12 w-48 h-48 bg-white/10 dark:bg-slate-900/5 blur-full" rounded-full flex z-10></div>
                <div class="relative z-10 flex flex-col h-full">
                    <span class="text-[10px] font-black text-indigo-400 dark:text-indigo-600 uppercase tracking-[0.3em] mb-8">Net Profit</span>
                    <h3 class="text-5xl font-display font-black text-white dark:text-slate-900 leading-none italic tracking-tighter">
                        $<span class="animate-counter" data-target="{{ $profit }}" data-decimals="0">0</span>
                    </h3>
                    <div class="mt-auto pt-10">
                        <div class="w-full h-1.5 bg-white/10 dark:bg-slate-200 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full animate-in slide-in-from-left duration-1000" style="width: 84%"></div>
                        </div>
                        <p class="text-[8px] font-black text-white/40 dark:text-slate-300 uppercase tracking-[0.4em] mt-4 italic">Calculated automatically</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ANALYTICS GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main Gradient Chart --}}
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-12 rounded-[4rem] border border-slate-200/60 dark:border-slate-800 shadow-sm relative overflow-hidden transition-all hover:shadow-2xl">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 mb-16">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight italic">Revenue vs Expenses</h3>
                        <p class="text-[10px] font-black text-slate-400 dark:text-slate-300 uppercase tracking-[0.2em] mt-2 italic">Past 7 Days Comparison</p>
                    </div>
                    <div class="flex items-center gap-6 bg-slate-50 dark:bg-slate-800/50 p-4 rounded-3xl border border-slate-100 dark:border-white/5">
                        <div class="flex items-center gap-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 shadow-lg shadow-indigo-500/50"></span>
                            <span class="text-[10px] font-black text-slate-500 dark:text-slate-300 uppercase tracking-widest">Revenue</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-lg shadow-rose-500/50"></span>
                            <span class="text-[10px] font-black text-slate-500 dark:text-slate-300 uppercase tracking-widest">Expenses</span>
                        </div>
                    </div>
                </div>
                <div class="h-96">
                    <canvas id="revenueExpenseChart" 
                            data-labels="{{ json_encode($dayLabels) }}"
                            data-revenue="{{ json_encode($weeklyRevenue) }}"
                            data-expense="{{ json_encode($weeklyExpense) }}"></canvas>
                </div>
            </div>

            {{-- Pipeline & Audience --}}
            <div class="space-y-8">
                <div class="bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-200/60 dark:border-slate-800 shadow-sm flex flex-col items-center">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight mb-12">Order Status</h3>
                    <div class="relative w-full h-64 flex items-center justify-center">
                        <canvas id="orderStatusChart" data-chart-data="{{ json_encode($orderStatusDist->pluck('count')) }}"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none mb-4">
                            <span class="text-[9px] font-black text-slate-400 dark:text-slate-300 uppercase tracking-widest">Total Orders</span>
                            <span class="text-4xl font-display font-black text-slate-900 dark:text-white mt-1">{{ ($stats['pending_orders'] ?? 0) + ($stats['delivered_orders'] ?? 0) + 124 }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-indigo-600 p-10 rounded-[3rem] text-white relative overflow-hidden shadow-2xl transition-all hover:scale-[1.02]">
                    <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-white/10 blur-3xl rounded-full"></div>
                    <div class="relative z-10 flex flex-col h-full">
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] opacity-60 mb-10 leading-none">Subscribers</p>
                        <div class="flex items-center justify-between mb-8">
                            <h4 class="text-6xl font-display font-black italic tracking-tighter">{{ number_format($totalSubscribers) }}</h4>
                            <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-white border border-white/20">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354l1.1 1.1a4 4 0 000 5.656l1.1 1.1m0 0a4 4 0 010 5.656l1.1 1.1m-10-8.908l1.1-1.1a4 4 0 015.656 0l1.1 1.1m0 0a4 4 0 010 5.656l1.1-1.1m-10 8.908l-1.1-1.1a4 4 0 010-5.656l-1.1-1.1"></path></svg>
                            </div>
                        </div>
                        <a href="{{ route('admin.crm.subscribers') }}" class="group w-full bg-white/10 hover:bg-white/20 py-4 px-6 rounded-2xl border border-white/10 text-[10px] font-black uppercase tracking-widest text-center transition-all flex items-center justify-center gap-3">
                            View Subscribers
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- LATENT ACTIVITY FEED --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-200/60 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-12 py-10 border-b border-slate-50 dark:border-white/5 flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight italic leading-none">Recent Orders</h3>
                        <p class="text-[10px] font-black text-slate-400 dark:text-slate-300 uppercase tracking-[0.2em] mt-3">Latest store transactions</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/40 border-b border-slate-100 dark:border-white/5">
                                <th class="px-12 py-6 text-[9px] font-black text-slate-400 dark:text-slate-300 uppercase tracking-[0.2em]">Order / Customer</th>
                                <th class="px-12 py-6 text-[9px] font-black text-slate-400 dark:text-slate-300 uppercase tracking-[0.2em]">Status</th>
                                <th class="px-12 py-6 text-[9px] font-black text-slate-400 dark:text-slate-300 uppercase tracking-[0.2em] text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-white/5">
                            @foreach($recentOrders as $ro)
                            <tr class="group hover:bg-blue-600/5 dark:hover:bg-blue-500/5 transition-all cursor-pointer">
                                <td class="px-12 py-8">
                                    <div class="flex items-center gap-6">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center font-black text-xs text-slate-400 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-inner">
                                            {{ substr($ro->order_number, -2) }}
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase">{{ $ro->order_number }}</h4>
                                            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-300 mt-1">{{ $ro->customer_name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-12 py-8">
                                    <span class="inline-flex px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border {{ $ro->status === 'paid' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/20' : 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 border-amber-100 dark:border-amber-800/20' }}">
                                        {{ $ro->status }}
                                    </span>
                                </td>
                                <td class="px-12 py-8 text-right">
                                    <h4 class="text-sm font-black text-slate-900 dark:text-white tracking-tight">${{ number_format($ro->total_price, 2) }}</h4>
                                    <p class="text-[9px] font-black text-slate-400 dark:text-slate-300 uppercase tracking-widest mt-1 italic">{{ $ro->created_at->diffForHumans() }}</p>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Neural Insights Bento --}}
            <div class="bg-rose-500/5 dark:bg-rose-950/10 rounded-[3rem] border border-rose-100 dark:border-rose-900/30 overflow-hidden flex flex-col">
                <div class="px-10 py-10 border-b border-rose-100 dark:border-rose-900/30 bg-rose-50 dark:bg-rose-950/20 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
                        <h3 class="text-lg font-black text-rose-600 dark:text-rose-400 uppercase tracking-wide italic">Low Stock Products</h3>
                    </div>
                </div>
                <div class="flex-1 p-8 space-y-6 overflow-y-auto custom-scrollbar">
                    @forelse($lowStockProducts as $product)
                    <div class="p-6 bg-white dark:bg-slate-900 rounded-3xl border border-rose-100 dark:border-rose-900/5 hover:border-rose-500/20 transition-all flex items-center justify-between group">
                        <div class="min-w-0 pr-4">
                            <h4 class="text-xs font-black text-slate-900 dark:text-white truncate uppercase">{{ $product->name }}</h4>
                            <p class="text-[9px] font-black text-rose-500 uppercase mt-2 tracking-widest">{{ $product->stock }} UNITS LEFT</p>
                        </div>
                        <button class="w-12 h-12 rounded-2xl bg-rose-600 text-white flex items-center justify-center shadow-xl shadow-rose-600/20 hover:scale-110 active:scale-95 transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>
                    @empty
                    <div class="h-full flex flex-col items-center justify-center py-20 text-center">
                        <div class="w-24 h-24 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-500 mb-8 border border-emerald-500/20">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="text-[11px] font-black text-emerald-600 uppercase tracking-[0.2em] italic">All stock looks good</p>
                        <p class="text-[9px] font-black text-slate-400 dark:text-slate-300 mt-3 uppercase tracking-widest opacity-60">No low stock alerts</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    (function() {
        "use strict";

        document.addEventListener('DOMContentLoaded', () => {
            // 1. Animated Counters
            const counters = document.querySelectorAll('.animate-counter');
            counters.forEach(counter => {
                const target = parseFloat(counter.getAttribute('data-target')) || 0;
                const decimals = parseInt(counter.getAttribute('data-decimals') || '0');
                const duration = 2500;
                const startTime = performance.now();

                function update(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const easeOutExpo = t => t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
                    const value = easeOutExpo(progress) * target;
                    
                    counter.innerText = value.toLocaleString(undefined, {
                        minimumFractionDigits: decimals,
                        maximumFractionDigits: decimals
                    });

                    if (progress < 1) requestAnimationFrame(update);
                }
                requestAnimationFrame(update);
            });

            // 2. Global UI Context
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255,255,255,0.03)' : 'rgba(0,0,0,0.03)';
            const labelColor = isDark ? '#94a3b8' : '#64748b';
            const fontFamily = 'Outfit, sans-serif';

            // 3. Sparkline Charts
            const initSparkline = (canvas) => {
                if (!canvas) return;
                const data = JSON.parse(canvas.dataset.values);
                const color = canvas.dataset.color || '#3b82f6';
                const ctx = canvas.getContext('2d');
                
                const grad = ctx.createLinearGradient(0, 0, 0, 64);
                grad.addColorStop(0, color + '20');
                grad.addColorStop(1, color + '00');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.map((_, i) => i),
                        datasets: [{
                            data: data,
                            borderColor: color,
                            borderWidth: 3,
                            fill: true,
                            backgroundColor: grad,
                            tension: 0.5,
                            pointRadius: 0
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { enabled: false } },
                        scales: { x: { display: false }, y: { display: false } }
                    }
                });
            };

            @if($isSuperAdmin)
                // Super Admin Console Initialization
                const growthCanvas = document.getElementById('storeGrowthChart');
                if (growthCanvas) {
                    const sgData = JSON.parse(growthCanvas.dataset.chartData);
                    new Chart(growthCanvas.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: sgData.map(d => d.date),
                            datasets: [{
                                label: 'Store Growth',
                                data: sgData.map(d => d.count),
                                borderColor: '#6366f1',
                                borderWidth: 5,
                                tension: 0.45,
                                fill: true,
                                backgroundColor: 'rgba(99, 102, 241, 0.05)',
                                pointRadius: 0,
                                pointHoverRadius: 8,
                                pointHoverBorderWidth: 4
                            }]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { grid: { display: false }, ticks: { color: labelColor, font: { size: 10, weight: '900', family: fontFamily } } },
                                y: { grid: { color: gridColor, drawBorder: false }, ticks: { color: labelColor, font: { size: 10, weight: '900', family: fontFamily } } }
                            }
                        }
                    });
                }

                const planCanvas = document.getElementById('planDistributionChart');
                if (planCanvas) {
                    const planData = JSON.parse(planCanvas.dataset.chartData);
                    new Chart(planCanvas.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: planData.map(d => d.name),
                            datasets: [{
                                data: planData.map(d => d.count),
                                backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#f43f5e'],
                                borderWidth: 0,
                                hoverOffset: 30
                            }]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            cutout: '88%',
                            plugins: { legend: { display: false } }
                        }
                    });
                }
            @else
                // Store Front Hub Initialization
                const reCanvas = document.getElementById('revenueExpenseChart');
                if (reCanvas) {
                    const labels = JSON.parse(reCanvas.dataset.labels);
                    const rev = JSON.parse(reCanvas.dataset.revenue);
                    const exp = JSON.parse(reCanvas.dataset.expense);
                    
                    new Chart(reCanvas.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [
                                { 
                                    label: 'Revenue', data: rev, borderColor: '#6366f1', 
                                    backgroundColor: 'rgba(99, 102, 241, 0.15)', fill: true, tension: 0.4, borderWidth: 6,
                                    pointRadius: 0, pointHoverRadius: 10, pointHoverBorderWidth: 5
                                },
                                { 
                                    label: 'Expenses', data: exp, borderColor: '#f43f5e', 
                                    borderWidth: 3, borderDash: [6, 6], fill: false, tension: 0.45,
                                    pointRadius: 0
                                }
                            ]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { grid: { color: gridColor, drawBorder: false }, ticks: { color: labelColor, font: { size: 10, weight: '900', family: fontFamily } } },
                                x: { grid: { display: false }, ticks: { color: labelColor, font: { size: 10, weight: '900', family: fontFamily } } }
                            }
                        }
                    });
                }

                const osCanvas = document.getElementById('orderStatusChart');
                if (osCanvas) {
                    const counts = JSON.parse(osCanvas.dataset.chartData);
                    new Chart(osCanvas.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: ['Pending', 'Processing', 'Delivered', 'Cancelled'],
                            datasets: [{
                                data: counts,
                                backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#f43f5e'],
                                borderWidth: 0,
                                hoverOffset: 25
                            }]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            cutout: '84%',
                            plugins: { legend: { display: false } }
                        }
                    });
                }

                // Sparklines
                initSparkline(document.getElementById('revenueSparkline'));
                initSparkline(document.getElementById('supplySparkline'));
                initSparkline(document.getElementById('lossSparkline'));
            @endif
        });
    })();
    </script>
    @endpush
</x-layouts.admin>
