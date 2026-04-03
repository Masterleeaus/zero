<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false', spotlight: false }"
      :class="{ 'dark': darkMode }">
<head>
    @php
        $store = auth()->user()->store ?? auth()->user()->ownedStores()->first();
        $companyName = $store ? $store->name : 'CommerceCore';
        $companyLogo = $store && $store->logo ? asset('storage/' . $store->logo) : asset('images/favicon.png');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — {{ $companyName }}</title>
    <link rel="icon" type="image/png" href="{{ $companyLogo }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 transition-colors duration-300 overflow-hidden">

    {{-- Toast Notification --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
         class="fixed top-5 right-5 z-[99] flex items-center gap-3 px-5 py-3.5 bg-blue-600 text-white rounded-xl shadow-lg shadow-blue-600/20">
        <div class="w-7 h-7 rounded bg-white/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        </div>
        <span class="font-medium text-sm">{{ session('success') }}</span>
    </div>
    @endif

    {{-- Quick Search (Spotlight) --}}
    <div x-show="spotlight" x-cloak @keydown.escape.window="spotlight = false" @keydown.meta.k.window.prevent="spotlight = !spotlight" @keydown.ctrl.k.window.prevent="spotlight = !spotlight"
         class="fixed inset-0 z-[100] flex items-start justify-center pt-[15vh]">
        <div @click="spotlight = false" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-xl bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden" @click.away="spotlight = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" placeholder="Search anything..." class="flex-1 bg-transparent border-none outline-none text-base text-slate-800 dark:text-white placeholder-slate-400 focus:ring-0 p-0" autofocus>
                <kbd class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-xs font-semibold text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-600">ESC</kbd>
            </div>
            <div class="px-6 py-6 text-center text-sm text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-700">
                Type above to quickly find pages, products, or orders.
            </div>
        </div>
    </div>

    <div class="flex h-screen overflow-hidden">

        {{-- ====== SIDEBAR ====== --}}
        <aside :class="sidebarOpen ? 'w-[280px]' : 'w-[80px]'"
               class="hidden lg:flex flex-col bg-white dark:bg-slate-800 transition-all duration-300 relative z-50 border-r border-slate-200 dark:border-slate-700"
               x-data="{ activeGroup: '{{ request()->is('admin/products*','admin/categories*','admin/orders*','admin/customers*','admin/coupons*') ? 'store' : (request()->is('admin/pos*') ? 'pos' : (request()->is('admin/accounts*','admin/transactions*','admin/assets*','admin/expenses*','admin/employees*','admin/attendance*','admin/payroll*','admin/leaves*','admin/reports*') ? 'backoffice' : (request()->is('admin/purchases*','admin/suppliers*','admin/inventory*','admin/branches*','admin/returns*') ? 'supply' : (request()->is('admin/logistics*','admin/shipments*') ? 'logistics' : (request()->is('admin/marketing*','admin/intelligence*','admin/subscribers*','admin/inquiries*','admin/agent*') ? 'crm' : (request()->is('admin/builder*') ? 'builder' : 'overview')))))) }}' }">

            {{-- Logo Area --}}
            <div class="flex items-center gap-3 px-6 h-[72px] flex-shrink-0 border-b border-slate-100 dark:border-slate-700/50">
                <div class="w-10 h-10 rounded bg-indigo-50 flex items-center justify-center flex-shrink-0">
                    <img src="{{ $companyLogo }}" alt="Logo" class="w-6 h-6 object-contain">
                </div>
                <div x-show="sidebarOpen" x-transition.opacity class="overflow-hidden">
                    <h1 class="font-bold text-lg text-slate-900 dark:text-white truncate leading-none">{{ $companyName }}</h1>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto px-4 space-y-6 custom-scrollbar pt-6 pb-6">
                
                {{-- OVERVIEW --}}
                <div class="space-y-1">
                    <p x-show="sidebarOpen" class="px-4 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Overview</p>
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                        <span x-show="sidebarOpen" class="text-sm">Dashboard</span>
                    </a>
                </div>

                {{-- STORE --}}
                <div class="space-y-1">
                    <p x-show="sidebarOpen" class="px-4 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Store</p>
                    
                    <div x-data="{ open: activeGroup === 'store' }">
                        <button @click="open = !open; activeGroup = open ? 'store' : ''" 
                                class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg transition-colors text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white"
                                :class="{ 'text-blue-600 dark:text-blue-400': open }">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                            <span x-show="sidebarOpen" class="flex-1 text-left text-sm font-medium">Catalog</span>
                            <svg x-show="sidebarOpen" class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                        </button>
                        <div x-show="open && sidebarOpen" x-collapse class="mt-1 ml-7 pl-3 border-l border-slate-200 dark:border-slate-700 space-y-1">
                            @foreach([['Products', 'admin.products.index'], ['Inventory', 'admin.inventory.index'], ['Point of Sale', 'admin.pos.index']] as $item)
                            <a href="{{ route($item[1]) }}" class="block py-1.5 px-3 rounded-md text-sm {{ request()->routeIs($item[1] . '*') ? 'text-blue-600 dark:text-blue-400 font-semibold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                {{ $item[0] }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- BACK OFFICE --}}
                <div class="space-y-1">
                    <p x-show="sidebarOpen" class="px-4 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Back Office</p>
                    <div x-data="{ open: activeGroup === 'backoffice' }">
                        <button @click="open = !open; activeGroup = open ? 'backoffice' : ''" 
                                class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg transition-colors text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white"
                                :class="{ 'text-blue-600 dark:text-blue-400': open }">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6.75h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.75m-.75 3h.75m-.75 3h.75"/></svg>
                            <span x-show="sidebarOpen" class="flex-1 text-left text-sm font-medium">Finance & HR</span>
                            <svg x-show="sidebarOpen" class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                        </button>
                        <div x-show="open && sidebarOpen" x-collapse class="mt-1 ml-7 pl-3 border-l border-slate-200 dark:border-slate-700 space-y-1">
                            @foreach([['Accounting', 'admin.accounts.index'], ['Employees', 'admin.employees.index'], ['Purchases', 'admin.purchases.index']] as $item)
                            <a href="{{ route($item[1]) }}" class="block py-1.5 px-3 rounded-md text-sm {{ request()->routeIs($item[1] . '*') ? 'text-blue-600 dark:text-blue-400 font-semibold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                {{ $item[0] }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- APP --}}
                <div class="space-y-1 border-t border-slate-100 dark:border-slate-700/50 pt-6">
                    <p x-show="sidebarOpen" class="px-4 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Apps & Extensions</p>
                    
                    {{-- BILLING --}}
                    <a href="{{ route('admin.billing.index') }}" 
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.billing.*') ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span x-show="sidebarOpen" class="flex-1 text-sm font-medium">Billing</span>
                    </a>
                    
                    {{-- BUILDER --}}
                    <a href="{{ route('admin.builder.index') }}" 
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.builder.*') ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9s2.015-9 4.5-9m0 18c-5.035 0-9-4.03-9-9s3.965-9 9-9 9 4.03 9 9-3.965 9-9 9z"/></svg>
                        <span x-show="sidebarOpen" class="flex-1 text-sm font-medium">Website Builder</span>
                    </a>

                    {{-- CRM --}}
                    <a href="{{ route('admin.crm.subscribers') }}" 
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.crm.*') ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a5.97 5.97 0 00-.94 3.197m.94-3.197a5.991 5.991 0 015.058-2.771L12 12.75l.001.001a5.991 5.991 0 015.058 2.771L18 15.526zM15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                        <span x-show="sidebarOpen" class="flex-1 text-sm font-medium">Customers & Marketing</span>
                    </a>
                </div>
            </nav>

            {{-- Sidebar Footer --}}
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700/50 flex-shrink-0">
                <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" 
                        class="w-full flex items-center justify-center p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition-colors border border-slate-200 dark:border-slate-600">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span x-show="sidebarOpen" class="ml-2 text-sm font-medium">Toggle Theme</span>
                </button>
            </div>
        </aside>

        {{-- ====== MAIN CONTENT ====== --}}
        <div class="flex-1 flex flex-col overflow-hidden relative">

            {{-- Header --}}
            <header class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md sticky top-0 px-6 h-[72px] flex items-center justify-between flex-shrink-0 z-40 border-b border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen; localStorage.setItem('sidebarOpen', sidebarOpen)" class="p-2 rounded-md text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-semibold text-slate-900 dark:text-white leading-tight">{{ $header ?? 'Dashboard' }}</h1>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    {{-- POS Shortcut --}}
                    <a href="{{ route('admin.pos.index') }}" class="flex items-center gap-2.5 px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-500/20 active:scale-95 group">
                        <svg class="w-4 h-4 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-1.218 2.1-3.218 2.147-4.14M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                        <span class="text-[10px] font-black uppercase tracking-widest hidden sm:inline">Sale</span>
                    </a>

                    <div class="h-6 w-[1px] bg-slate-200 dark:bg-slate-700 mx-1"></div>

                    {{-- Spotlight Trigger --}}
                    <button @click="spotlight = true" class="hidden md:flex items-center gap-3 px-4 py-2 bg-slate-100 dark:bg-slate-700/50 rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors border border-transparent">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span class="text-sm font-medium">Search</span>
                        <kbd class="px-2 py-0.5 rounded bg-white dark:bg-slate-800 text-[10px] font-semibold text-slate-400 border border-slate-200 dark:border-slate-600">⌘K</kbd>
                    </button>

                    <div class="h-6 w-[1px] bg-slate-200 dark:bg-slate-700 mx-1"></div>

                    {{-- User Profile --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-3 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors">
                            <div class="w-8 h-8 rounded-md bg-blue-600 flex items-center justify-center text-white text-sm font-bold shadow-sm">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="hidden md:block text-left mr-1">
                                <p class="text-sm font-semibold text-slate-800 dark:text-white leading-none">{{ auth()->user()->name }}</p>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 mr-1 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg py-2 z-50">
                            <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-700 mb-2">
                                <p class="text-sm text-slate-500 dark:text-slate-400">Signed in as</p>
                                <p class="text-sm font-semibold text-slate-800 dark:text-white truncate">{{ auth()->user()->email }}</p>
                            </div>
                            @php
                                $settingsStore = auth()->user()->store ?? auth()->user()->ownedStores()->first() ?? \App\Models\Store::first();
                            @endphp
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Profile
                            </a>
                            @if($settingsStore)
                            <a href="{{ route('admin.stores.settings', $settingsStore) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Store Settings
                            </a>
                            @endif
                            <div class="h-[1px] bg-slate-100 dark:bg-slate-700 my-2"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Body --}}
            <main class="flex-1 overflow-y-auto relative z-10 custom-scrollbar p-6">
                <div class="max-w-[1600px] mx-auto">
                    {{ $slot }}
                </div>
            </main>
        </div>

        {{-- AI Assistant Floating Trigger --}}
        <div x-data="{ open: false }" class="fixed bottom-6 right-6 z-[100]">
            <button @click="open = !open" 
                    class="w-14 h-14 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-lg hover:-translate-y-1 transition-transform group">
                <svg x-show="!open" class="w-6 h-6 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                <svg x-show="open" x-cloak class="w-6 h-6 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            
            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 @click.away="open = false"
                 class="absolute bottom-20 right-0 w-[380px] h-[500px] bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col">
                <div class="p-4 bg-blue-600 text-white">
                    <h3 class="font-semibold text-lg">Smart Assistant</h3>
                    <p class="text-sm text-blue-100">Help, support, and automation</p>
                </div>
                <div class="flex-1 p-4 space-y-4 overflow-y-auto bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div class="bg-white dark:bg-slate-800 p-3 rounded-2xl rounded-tl-none border border-slate-200 dark:border-slate-700 shadow-sm max-w-[85%]">
                            <p class="text-sm text-slate-700 dark:text-slate-300">Hello! I am your store's assistant. Ask me to generate a report, explain a metric, or help you navigate.</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                    <div class="relative">
                        <input type="text" placeholder="Type a request..." class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2 text-sm focus:ring-1 focus:ring-blue-500 outline-none pr-10">
                        <button class="absolute right-2 top-1/2 -translate-y-1/2 text-blue-600 p-1 hover:bg-blue-50 rounded">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
    <div id="portal-root"></div>
</body>
</html>
