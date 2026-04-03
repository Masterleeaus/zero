<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Checkout</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800|outfit:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        @keyframes scaleUp { from { opacity: 0; transform: scale(0.95) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
        .animate-scale-up { animation: scaleUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .btn-glow:hover { box-shadow: 0 0 20px rgba(59, 130, 246, 0.4); transform: translateY(-1px); }
        .btn-glow:active { transform: translateY(0); }
    </style>
</head>
<body class="font-sans antialiased text-slate-900 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 h-screen overflow-hidden selection:bg-blue-500 selection:text-white transition-colors duration-300">
    
    <div x-data="posSystem()" x-init="fetchHeldOrders()"
         @keydown.ctrl.f.window.prevent="$refs.searchInput.focus()"
         @keydown.escape.window="if(isPaymentModalOpen) isPaymentModalOpen = false; if(isHoldModalOpen) isHoldModalOpen = false; if(isHeldOrdersModalOpen) isHeldOrdersModalOpen = false; if(isHistoryModalOpen) isHistoryModalOpen = false"
         class="flex h-full w-full">
         
        {{-- ================= LEFT SIDE: PRODUCT GRID & CATEGORIES (65%) ================= --}}
        <main class="w-[65%] h-full flex flex-col bg-white dark:bg-slate-900 border-r border-slate-200/60 dark:border-slate-800/60 relative z-10 transition-colors duration-300">
            
            {{-- Header --}}
            <header class="h-[80px] bg-white dark:bg-slate-900 flex items-center justify-between px-8 border-b border-slate-100 dark:border-slate-800 shrink-0">
                <div class="flex items-center gap-5 w-1/3">
                    <a href="{{ route('admin.dashboard') }}" class="group p-3 rounded-2xl text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-300">
                        <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                    <div>
                        <h1 class="font-display font-black text-xl leading-tight text-slate-900 dark:text-white uppercase tracking-tight">Terminal 01</h1>
                        <p class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.2em] flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            Live System
                        </p>
                    </div>
                </div>

                <div class="flex-1 max-w-xl">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400 group-focus-within:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input x-ref="searchInput" x-model="searchQuery" type="text" autofocus
                               class="block w-full pl-12 pr-14 py-4 bg-slate-50 dark:bg-slate-800/50 border-transparent rounded-[1.25rem] focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/50 text-sm font-semibold transition-all shadow-inner placeholder-slate-400"
                               placeholder="Registry scan or search items...">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <kbd class="px-2 py-1 rounded-lg bg-white dark:bg-slate-700 text-[9px] font-black text-slate-400 border border-slate-200 dark:border-slate-600 shadow-sm shadow-black/5">⌘F</kbd>
                        </div>
                    </div>
                </div>
                
                <div class="w-1/3 flex justify-end items-center gap-3">
                    <button @click="isHistoryModalOpen = true; fetchPosHistory()" class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </button>
                    <button @click="isHeldOrdersModalOpen = true" class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-all shadow-sm relative">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        <span x-show="heldOrders.length > 0" class="absolute -top-1 -right-1 w-5 h-5 text-[10px] font-black text-white bg-amber-500 flex items-center justify-center rounded-full border-2 border-white dark:border-slate-900 shadow-md" x-text="heldOrders.length"></span>
                    </button>
                    <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all shadow-sm">
                        <svg x-show="!darkMode" class="w-5 h-5 transition-transform duration-500 rotate-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <svg x-show="darkMode" x-cloak class="w-5 h-5 transition-transform duration-500 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </button>
                    <button @click="toggleFullscreen()" class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                    </button>
                </div>
            </header>

            {{-- Categories Scrollbar --}}
            <div class="h-[70px] bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 flex items-center px-8 gap-3 overflow-x-auto shrink-0 no-scrollbar relative z-10 transition-colors duration-300">
                <button @click="selectedCategory = null" 
                        :class="selectedCategory === null ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/25 ring-4 ring-blue-500/10' : 'bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'"
                        class="px-6 py-2.5 rounded-[1rem] font-black text-[10px] uppercase tracking-[0.2em] transition-all whitespace-nowrap">
                    All Categories
                </button>
                @foreach($categories as $category)
                <button @click="selectedCategory = {{ $category->id }}"
                        :class="selectedCategory === {{ $category->id }} ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/25 ring-4 ring-blue-500/10' : 'bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'"
                        class="px-6 py-2.5 rounded-[1rem] font-bold text-xs transition-all whitespace-nowrap flex items-center gap-2">
                    {{ $category->name }}
                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-black"
                          :class="selectedCategory === {{ $category->id }} ? 'bg-white/20' : 'bg-slate-200 dark:bg-slate-700'">{{ $category->products_count }}</span>
                </button>
                @endforeach
            </div>

            {{-- Product Grid --}}
            <div class="flex-1 overflow-y-auto p-8 scroll-smooth bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
                    @foreach($products as $product)
                    <div x-show="matchesSearch('{{ addslashes($product->name) }}', '{{ $product->sku }}', {{ $product->category_id ?? 'null' }})" 
                         @click="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }}, '{{ collect($product->image)->first() ? asset('storage/'.collect($product->image)->first()) : '' }}')"
                         class="group bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 p-3 cursor-pointer hover:border-blue-400 dark:hover:border-blue-500 hover:shadow-2xl hover:shadow-blue-500/10 transition-all duration-500 flex flex-col h-[280px] select-none relative overflow-hidden">
                        
                        {{-- Stock Badge --}}
                        <div class="absolute top-5 right-5 z-20 px-3 py-1.5 rounded-2xl text-[10px] font-black shadow-md backdrop-blur-md uppercase tracking-wider transition-all duration-300"
                             :class="{{ $product->stock }} > 10 ? 'bg-white/95 dark:bg-slate-900/95 text-slate-900 dark:text-white border border-slate-100 dark:border-slate-800' : 'bg-rose-500 text-white shadow-lg shadow-rose-500/20'">
                            {{ $product->stock }} UNITS
                        </div>

                        {{-- Image --}}
                        <div class="h-[140px] w-full bg-slate-50 dark:bg-slate-800 rounded-[1.5rem] overflow-hidden relative transition-all duration-500 group-hover:scale-[0.98]">
                            @if(collect($product->image)->first())
                                <img src="{{ asset('storage/' . collect($product->image)->first()) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" loading="lazy">
                            @else
                                <div class="absolute inset-0 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 flex items-center justify-center">
                                    <svg class="h-10 w-10 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-blue-600/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-[2px]">
                                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-xl transform translate-y-4 group-hover:translate-y-0 transition-all duration-500">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Info --}}
                        <div class="px-2 pt-4 pb-2 flex-1 flex flex-col justify-between">
                            <h3 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] line-clamp-1 mb-1">{{ $product->category->name ?? 'Registry Item' }}</h3>
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white line-clamp-2 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $product->name }}</h4>
                            <div class="mt-auto pt-3 flex items-center justify-between">
                                <div class="text-lg font-display font-black text-slate-900 dark:text-white tracking-tighter">${{ number_format($product->price, 2) }}</div>
                                <div class="w-8 h-8 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </main>

        {{-- ================= RIGHT SIDE: CART PANEL (35%) ================= --}}
        <aside class="w-[35%] h-full flex flex-col bg-white dark:bg-slate-900 shadow-[0_0_40px_-10px_rgba(0,0,0,0.1)] relative z-20 transition-colors duration-300">
            
            {{-- Customer & Cart Header --}}
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 shrink-0">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-display font-black text-lg text-slate-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        Current Sale
                    </h2>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-[10px] font-black tracking-widest text-blue-600 dark:text-blue-400 uppercase" x-text="cart.length + ' Items'"></span>
                        <button @click="clearCart()" :disabled="cart.length === 0" class="p-2 rounded-xl text-rose-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 disabled:opacity-30 disabled:hover:bg-transparent transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button @click="isAddCustomerModalOpen = true" class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors shadow-inner">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    </button>
                    <div class="relative flex-1">
                        <div class="bg-slate-50 dark:bg-slate-800 rounded-xl px-4 py-2 flex flex-col justify-center border border-transparent hover:border-slate-200 dark:hover:border-slate-700 transition-colors cursor-text group" @click="$refs.custInput.focus()">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Customer</p>
                            <input x-ref="custInput" 
                                   x-model="customerQuery" 
                                   @input.debounce.300ms="searchCustomers()"
                                   @focus="isCustomerSearchOpen = true"
                                   type="text" 
                                   placeholder="Search or Walk-in Customer" 
                                   class="w-full bg-transparent border-none p-0 text-sm font-semibold text-slate-900 dark:text-white placeholder-slate-400 focus:ring-0">
                        </div>
                        
                        {{-- Customer Search Results --}}
                        <div x-show="isCustomerSearchOpen && (searchResults.length > 0 || customerQuery.length > 2)" 
                             @click.away="isCustomerSearchOpen = false"
                             class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 z-[100] max-h-[300px] overflow-y-auto animate-scale-up" x-cloak>
                            
                            <template x-for="result in searchResults" :key="result.id">
                                <button @click="selectCustomer(result)" class="w-full px-4 py-3 flex items-center gap-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors border-b border-slate-100 dark:border-slate-700 last:border-none text-left">
                                    <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold shrink-0" x-text="result.name.charAt(0)"></div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-slate-900 dark:text-white truncate" x-text="result.name"></p>
                                        <p class="text-[10px] text-slate-500 font-medium" x-text="result.phone || result.email || 'No contact info'"></p>
                                    </div>
                                </button>
                            </template>

                            <div x-show="searchResults.length === 0 && customerQuery.length > 2" class="p-4 text-center">
                                <p class="text-xs text-slate-500 mb-3">No customer found matching "<span x-text="customerQuery"></span>"</p>
                                <button @click="isAddCustomerModalOpen = true; isCustomerSearchOpen = false; newCustomer.name = customerQuery" class="w-full py-2 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-700 transition-colors">
                                    + Register New Customer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cart Items List (Scrollable) --}}
            <div class="flex-1 overflow-y-auto p-2 bg-slate-50/50 dark:bg-slate-900/50 relative">
                
                {{-- Empty State --}}
                <div x-show="cart.length === 0" class="absolute inset-0 flex flex-col items-center justify-center p-8 text-center text-slate-400">
                    <div class="w-24 h-24 mb-6 rounded-3xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center shadow-inner">
                        <svg class="w-12 h-12 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <p class="font-display font-bold text-xl text-slate-700 dark:text-slate-300 mb-2">Cart is empty</p>
                    <p class="text-sm">Scan a barcode or tap items in the grid to add them to the sale.</p>
                </div>

                <div class="space-y-2 p-3">
                    <template x-for="item in cart" :key="item.id">
                        <div class="bg-white dark:bg-slate-800 p-3 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700 flex items-center gap-4 group relative hover:border-blue-300 dark:hover:border-blue-600 transition-colors">
                            
                            {{-- Image --}}
                            <div class="w-[60px] h-[60px] bg-slate-100 dark:bg-slate-900 rounded-xl overflow-hidden shrink-0">
                                <template x-if="item.image">
                                    <img :src="item.image" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!item.image">
                                    <div class="w-full h-full flex items-center justify-center text-slate-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                </template>
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0 py-1">
                                <h4 class="text-sm font-semibold text-slate-900 dark:text-white truncate mb-1" x-text="item.name"></h4>
                                <div class="text-blue-600 dark:text-blue-400 font-bold text-sm leading-none" x-text="formatCurrency(item.price)"></div>
                            </div>

                            {{-- Quantity Stepper --}}
                            <div class="flex flex-col items-center justify-between h-[60px] w-8 shrink-0 bg-slate-50 dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                                <button @click="updateQuantity(item.id, item.quantity + 1)" class="w-full h-[35%] flex items-center justify-center text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/></svg>
                                </button>
                                <span class="text-xs font-bold text-slate-900 dark:text-white flex-1 flex items-center justify-center" x-text="item.quantity"></span>
                                <button @click="updateQuantity(item.id, item.quantity - 1)" class="w-full h-[35%] flex items-center justify-center text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Totals & Checkout Panel (Fixed Bottom) --}}
            <div class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 p-6 shrink-0 shadow-[0_-10px_40px_-10px_rgba(0,0,0,0.1)] relative z-20">
                
                {{-- Quick Discount/Actions --}}
                <div class="flex items-center gap-3 mb-5">
                    <button class="flex-1 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        Discount
                    </button>
                    <button @click="isHoldModalOpen = true; setTimeout(() => $refs.holdRefInput.focus(), 50)" :disabled="cart.length === 0" class="flex-1 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        Save Cart
                    </button>
                </div>

                {{-- Computation --}}
                <div class="space-y-2.5 mb-6">
                    <div class="flex justify-between text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                        <span>Items Total</span>
                        <span x-text="formatCurrency(subtotal)"></span>
                    </div>
                    <div class="flex justify-between text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                        <span>Service Tax</span>
                        <span x-text="formatCurrency(tax)"></span>
                    </div>
                    <div class="flex justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-xl font-display font-black text-slate-900 dark:text-white uppercase tracking-tighter">Grand Total</span>
                        <span class="text-3xl font-display font-black text-blue-600 dark:text-blue-400" x-text="formatCurrency(total)"></span>
                    </div>
                </div>
                
                {{-- Payment Buttons --}}
                <div class="grid grid-cols-2 gap-3">
                    <button @click="paymentMethod = 'cash'; openPaymentModal()" :disabled="cart.length === 0" class="btn-glow flex flex-col items-center justify-center gap-1.5 py-4 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white font-bold shadow-lg shadow-emerald-500/30 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Cash (F8)
                    </button>
                    <button @click="paymentMethod = 'card'; processCardPayment()" :disabled="cart.length === 0" class="btn-glow flex flex-col items-center justify-center gap-1.5 py-4 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-950 dark:from-slate-700 dark:to-slate-800 text-white font-bold shadow-lg shadow-slate-900/30 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Card (F9)
                    </button>
                </div>
            </div>
        </aside>

        {{-- ================= PAYMENT MODAL ================= --}}
        <div x-show="isPaymentModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center pt-8 bg-slate-900/40 backdrop-blur-md" x-transition.opacity x-cloak>
            <div @click.away="isPaymentModalOpen = false" class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-2xl w-full max-w-[480px] overflow-hidden border border-slate-200 dark:border-slate-700 animate-scale-up">
                <div class="px-8 py-5 flex justify-between items-center border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <h3 class="font-display font-bold text-xl text-slate-900 dark:text-white">Cash Provided</h3>
                    </div>
                    <button @click="isPaymentModalOpen = false" class="w-8 h-8 flex items-center justify-center rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-8">
                    <div class="text-center mb-8">
                        <p class="text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider text-[11px] mb-1">Total Due</p>
                        <p class="font-display font-black text-[3.5rem] leading-none text-slate-900 dark:text-white" x-text="formatCurrency(total)"></p>
                    </div>
                    <div class="mb-6 relative">
                        <label class="absolute -top-2.5 left-4 px-1.5 bg-white dark:bg-slate-900 text-[11px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest z-10">Amount Received</label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                <span class="text-xl font-bold text-slate-400">$</span>
                            </div>
                            <input id="payment-input" type="number" x-model="amountTendered" step="0.01" 
                                   @keyup.enter="completeSale()"
                                   class="block w-full pl-10 pr-5 py-5 text-3xl font-bold bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-0 focus:border-blue-500 dark:focus:border-blue-400 transition-colors" placeholder="0.00">
                        </div>
                    </div>
                    <div class="grid grid-cols-4 gap-3 mb-8">
                        <button @click="amountTendered = total; document.getElementById('payment-input').focus();" class="col-span-1 py-4 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 rounded-xl font-bold text-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors border border-emerald-200 dark:border-emerald-800">Exact</button>
                        <button @click="addAmountTendered(10)" class="py-4 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-xl font-bold text-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors border border-slate-200 dark:border-slate-700">+$10</button>
                        <button @click="addAmountTendered(50)" class="py-4 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-xl font-bold text-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors border border-slate-200 dark:border-slate-700">+$50</button>
                        <button @click="addAmountTendered(100)" class="py-4 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-xl font-bold text-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors border border-slate-200 dark:border-slate-700">+$100</button>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800 rounded-2xl p-5 mb-8 border border-slate-200 dark:border-slate-700 flex justify-between items-center">
                        <span class="text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-xs">Change Due</span>
                        <span class="text-2xl font-black" :class="changeDue >= 0 ? 'text-emerald-500' : 'text-rose-500'" x-text="formatCurrency(changeDue)"></span>
                    </div>
                    <button @click="completeSale()" :disabled="changeDue < 0 || isProcessing" class="btn-glow w-full py-5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-2xl font-bold text-lg shadow-xl shadow-blue-500/25 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-3">
                        <span x-show="!isProcessing">Complete Order (Enter)</span>
                        <span x-show="isProcessing">Processing Payment...</span>
                        <svg x-show="isProcessing" x-cloak class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    </button>
                </div>
            </div>
        </div>
        
        {{-- ================= INVOICE SELECTION MODAL ================= --}}
        <div x-show="isInvoiceModalOpen" class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm" x-transition.opacity x-cloak>
            <div @click.away="isInvoiceModalOpen = false" class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl w-full max-w-[440px] overflow-hidden border border-slate-200 dark:border-slate-700 animate-scale-up p-8 text-center">
                <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="font-display font-black text-2xl text-slate-900 dark:text-white mb-2">Sale Complete!</h3>
                <p class="text-slate-500 dark:text-slate-400 mb-8 font-medium">Choose your preferred invoice format to print or download.</p>
                
                <div class="grid grid-cols-2 gap-4">
                    <button @click="printInvoice('thermal')" class="group p-5 rounded-3xl bg-slate-50 dark:bg-slate-800 border-2 border-transparent hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all text-center">
                        <div class="w-12 h-12 bg-white dark:bg-slate-700 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-sm group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-slate-600 dark:text-slate-300 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        </div>
                        <span class="block font-bold text-slate-900 dark:text-white">Thermal</span>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">80mm Receipt</span>
                    </button>
                    
                    <button @click="printInvoice('a4')" class="group p-5 rounded-3xl bg-slate-50 dark:bg-slate-800 border-2 border-transparent hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all text-center">
                        <div class="w-12 h-12 bg-white dark:bg-slate-700 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-sm group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-slate-600 dark:text-slate-300 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <span class="block font-bold text-slate-900 dark:text-white">A4 Format</span>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Standard PDF</span>
                    </button>
                </div>
                
                <button @click="isInvoiceModalOpen = false" class="mt-8 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 font-bold text-sm tracking-wide uppercase px-6 py-2">Skip & Next Sale</button>
            </div>
        </div>

        {{-- ================= CUSTOMER REGISTRATION MODAL ================= --}}
        <div x-show="isAddCustomerModalOpen" class="fixed inset-0 z-[120] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm" x-transition.opacity x-cloak>
            <div @click.away="isAddCustomerModalOpen = false" class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-2xl w-full max-w-[400px] overflow-hidden border border-slate-200 dark:border-slate-700 animate-scale-up">
                <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-display font-bold text-xl text-slate-900 dark:text-white">Register New Customer</h3>
                </div>
                <div class="p-8 space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Full Name</label>
                        <input type="text" x-model="newCustomer.name" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Phone Number</label>
                        <input type="text" x-model="newCustomer.phone" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Email Address</label>
                        <input type="email" x-model="newCustomer.email" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    </div>
                    <button @click="registerCustomer()" :disabled="isProcessingCustomer" class="w-full py-4 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition-all mt-4">
                        <span x-show="!isProcessingCustomer">Save Customer</span>
                        <span x-show="isProcessingCustomer">Saving...</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ================= HOLD ORDER MODAL ================= --}}
        <div x-show="isHoldModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-md" x-transition.opacity x-cloak>
            <div @click.away="isHoldModalOpen = false" class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-2xl w-full max-w-[400px] overflow-hidden border border-slate-200 dark:border-slate-700 animate-scale-up">
                <div class="p-8">
                    <h3 class="font-display font-bold text-xl text-slate-900 dark:text-white mb-2">Save Cart for Later</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Enter a reference name to quickly find it later (e.g. customer name).</p>
                    
                    <div class="relative mb-6">
                        <input x-ref="holdRefInput" type="text" x-model="holdReference" placeholder="Reference name..." class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    </div>
                    
                    <div class="flex gap-3">
                        <button @click="isHoldModalOpen = false" class="flex-1 py-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">Cancel</button>
                        <button @click="processHoldOrder()" :disabled="isProcessingHold" class="flex-1 py-3 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition-all">Save Order</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= HELD ORDERS MODAL ================= --}}
        <div x-show="isHeldOrdersModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-md" x-transition.opacity x-cloak>
            <div @click.away="isHeldOrdersModalOpen = false" class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-2xl w-full max-w-[500px] max-h-[80vh] flex flex-col overflow-hidden border border-slate-200 dark:border-slate-700 animate-scale-up">
                <div class="px-8 py-5 flex justify-between items-center border-b border-slate-100 dark:border-slate-800 shrink-0">
                    <h3 class="font-display font-bold text-xl text-slate-900 dark:text-white">Held Orders</h3>
                    <button @click="isHeldOrdersModalOpen = false" class="w-8 h-8 flex items-center justify-center rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <div class="flex-1 overflow-y-auto p-4 bg-slate-50 dark:bg-slate-950">
                    <div x-show="heldOrders.length === 0" class="flex flex-col items-center justify-center h-full text-slate-400 py-12">
                        <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        <p class="font-medium">No held orders found.</p>
                    </div>

                    <div class="space-y-3">
                        <template x-for="held in heldOrders" :key="held.id">
                            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-sm">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-bold text-slate-900 dark:text-white" x-text="held.reference"></span>
                                        <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-[10px] font-bold" x-text="new Date(held.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})"></span>
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400" x-text="held.cart_data.length + ' items · ' + formatCurrency(held.total)"></div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="deleteHeldOrder(held.id)" class="p-2 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors tooltip-trigger relative">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                    <button @click="recallOrder(held)" class="px-4 py-2 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-bold text-sm hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
                                        Restore
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= POS HISTORY MODAL ================= --}}
        <div x-show="isHistoryModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-md" x-transition.opacity x-cloak>
            <div @click.away="isHistoryModalOpen = false" class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-2xl w-full max-w-[600px] max-h-[80vh] flex flex-col overflow-hidden border border-slate-200 dark:border-slate-700 animate-scale-up">
                <div class="px-8 py-5 flex justify-between items-center border-b border-slate-100 dark:border-slate-800 shrink-0">
                    <h3 class="font-display font-bold text-xl text-slate-900 dark:text-white flex items-center gap-3">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Daily Activity (<span x-text="posHistory.length"></span>)
                    </h3>
                    <button @click="isHistoryModalOpen = false" class="w-8 h-8 flex items-center justify-center rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <div class="flex-1 overflow-y-auto p-4 bg-slate-50 dark:bg-slate-950">
                    <div x-show="posHistory.length === 0" class="flex flex-col items-center justify-center h-full text-slate-400 py-12">
                        <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="font-medium">No sales today yet.</p>
                    </div>

                    <div class="space-y-3">
                        <template x-for="txn in posHistory" :key="txn.id">
                            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-sm">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-bold text-slate-900 dark:text-white" x-text="txn.order_number"></span>
                                        <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-[10px] font-bold" x-text="txn.time"></span>
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400" x-text="txn.items_count + ' items · ' + formatCurrency(txn.total_price)"></div>
                                </div>
                                <div>
                                    <button @click="window.open(`/admin/orders/${txn.id}/invoice/thermal`, '_blank', 'width=400,height=600')" class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-sm hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        Receipt
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

    <script>
        function posSystem() {
            return {
                searchQuery: '',
                selectedCategory: null,
                cart: [],
                customer: { name: '', phone: '' },
                paymentMethod: 'cash',
                isPaymentModalOpen: false,
                amountTendered: '',
                isProcessing: false,
                lastProcessedOrderId: null,

                // Hold Orders specific state
                isHoldModalOpen: false,
                isProcessingHold: false,
                holdReference: '',
                isHeldOrdersModalOpen: false,
                heldOrders: [],

                // History specific state
                isHistoryModalOpen: false,
                posHistory: [],

                // Invoice selection modal
                isInvoiceModalOpen: false,
                // Customer related state
                customerQuery: '',
                isCustomerSearchOpen: false,
                searchResults: [],
                isAddCustomerModalOpen: false,
                isProcessingCustomer: false,
                newCustomer: { name: '', phone: '', email: '' },
                selectedCustomerData: null,
                
                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },
                get tax() { return 0; },
                get total() { return this.subtotal + this.tax; },
                get changeDue() {
                    return (parseFloat(this.amountTendered) || 0) - this.total;
                },

                matchesSearch(name, sku, categoryId) {
                    const matchesCategory = this.selectedCategory === null || this.selectedCategory === categoryId;
                    const matchesText = name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                                        sku.toLowerCase().includes(this.searchQuery.toLowerCase());
                    return matchesCategory && matchesText;
                },

                formatCurrency(value) {
                    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 }).format(value);
                },

                addToCart(id, name, price, image) {
                    const existingItem = this.cart.find(item => item.id === id);
                    if (existingItem) {
                        existingItem.quantity += 1;
                    } else {
                        this.cart.unshift({ id, name, price, image, quantity: 1 });
                    }
                    this.playBeep();
                },
                
                updateQuantity(id, newQuantity) {
                    if (newQuantity < 1) {
                        this.removeFromCart(id);
                        return;
                    }
                    const item = this.cart.find(item => item.id === id);
                    if (item) item.quantity = newQuantity;
                },
                
                removeFromCart(id) {
                    this.cart = this.cart.filter(item => item.id !== id);
                },
                
                clearCart() {
                    this.playBeep(400);
                    this.cart = [];
                    this.customer = { name: '', phone: '' };
                    this.amountTendered = '';
                },

                openPaymentModal() {
                    this.isPaymentModalOpen = true;
                    setTimeout(() => {
                        const input = document.getElementById('payment-input');
                        if(input) { input.focus(); input.select(); }
                    }, 50);
                },
                
                addAmountTendered(amount) {
                    const current = parseFloat(this.amountTendered) || 0;
                    this.amountTendered = (current + amount).toFixed(2);
                    document.getElementById('payment-input').focus();
                },
                
                processCardPayment() {
                    alert("Credit Card Terminal Activated. Please tap card.");
                    this.paymentMethod = 'card';
                    this.completeSale();
                },

                async completeSale() {
                    if (this.cart.length === 0 || this.isProcessing) return;
                    this.isProcessing = true;

                    try {
                        const response = await fetch("{{ route('admin.pos.checkout') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                "Accept": "application/json"
                            },
                            body: JSON.stringify({
                                customer_id: this.selectedCustomerData ? this.selectedCustomerData.id : null,
                                customer_name: this.customer.name || this.customerQuery || 'Walk-in Customer',
                                customer_phone: this.customer.phone,
                                payment_method: this.paymentMethod,
                                amount_paid: this.paymentMethod === 'cash' ? parseFloat(this.amountTendered) : this.total,
                                items: this.cart.map(item => ({ id: item.id, quantity: item.quantity, price: item.price }))
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            // Immediate UI Feedback
                            this.playSuccessBeep();
                            this.isPaymentModalOpen = false;
                            
                            // Set Order Data and Show Success Modal immediately
                            this.lastProcessedOrderId = data.order_id;
                            this.isInvoiceModalOpen = true;
                            
                            // Cleanup other states in the background
                            this.clearCart();
                            this.selectedCustomerData = null;
                            this.customerQuery = '';
                            this.customer = { name: '', phone: '' };
                            this.amountTendered = '';
                        } else {
                            alert(data.message || 'Error processing sale.');
                        }
                    } catch (error) {
                        alert('Network Error. Please try again.');
                    } finally {
                        this.isProcessing = false;
                    }
                },

                // --- CUSTOMER LOGIC ---

                async searchCustomers() {
                    if (this.customerQuery.length < 2) {
                        this.searchResults = [];
                        return;
                    }

                    try {
                        const res = await fetch(`/admin/pos/customers/search?q=${encodeURIComponent(this.customerQuery)}`, {
                            headers: { "Accept": "application/json" }
                        });
                        if (res.ok) {
                            this.searchResults = await res.json();
                        }
                    } catch (e) { console.error(e); }
                },

                selectCustomer(cust) {
                    this.selectedCustomerData = cust;
                    this.customer.name = cust.name;
                    this.customer.phone = cust.phone;
                    this.customerQuery = cust.name;
                    this.isCustomerSearchOpen = false;
                    this.playBeep(600);
                },

                async registerCustomer() {
                    if (!this.newCustomer.name || this.isProcessingCustomer) return;
                    this.isProcessingCustomer = true;

                    try {
                        const res = await fetch("{{ route('admin.pos.customers.register') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                "Accept": "application/json"
                            },
                            body: JSON.stringify(this.newCustomer)
                        });

                        const data = await res.json();
                        if (res.ok) {
                            this.selectCustomer(data.customer);
                            this.isAddCustomerModalOpen = false;
                            this.newCustomer = { name: '', phone: '', email: '' };
                        } else {
                            alert(data.message || 'Error registering customer.');
                        }
                    } catch (e) {
                        alert('Network Error.');
                    } finally {
                        this.isProcessingCustomer = false;
                    }
                },

                printInvoice(type) {
                    if (!this.lastProcessedOrderId) return;
                    window.open(`/admin/orders/${this.lastProcessedOrderId}/invoice/${type}`, '_blank', 'width=800,height=900');
                    this.isInvoiceModalOpen = false;
                },

                // --- HELD ORDERS & HISTORY LOGIC ---

                async fetchPosHistory() {
                    try {
                        const res = await fetch("{{ route('admin.pos.history') }}", { headers: { "Accept": "application/json" }});
                        if(res.ok) this.posHistory = await res.json();
                    } catch(e) {}
                },

                async fetchHeldOrders() {
                    try {
                        const res = await fetch("{{ route('admin.pos.held') }}", {
                            headers: { "Accept": "application/json" }
                        });
                        if(res.ok) {
                            this.heldOrders = await res.json();
                        }
                    } catch(e) {}
                },

                async processHoldOrder() {
                    if(!this.holdReference || this.cart.length === 0 || this.isProcessingHold) return;
                    this.isProcessingHold = true;

                    try {
                        const res = await fetch("{{ route('admin.pos.hold') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                "Accept": "application/json"
                            },
                            body: JSON.stringify({
                                reference: this.holdReference,
                                cart_data: this.cart,
                                customer_data: this.customer,
                                subtotal: this.subtotal
                            })
                        });

                        const data = await res.json();
                        if (res.ok) {
                            this.isHoldModalOpen = false;
                            this.holdReference = '';
                            this.clearCart();
                            this.fetchHeldOrders();
                        } else {
                            alert(data.message || 'Error holding order.');
                        }
                    } catch (e) {
                        alert('Network Error.');
                    } finally {
                        this.isProcessingHold = false;
                    }
                },

                async recallOrder(held) {
                    if(this.cart.length > 0) {
                        if(!confirm('Current cart will be cleared. Continue?')) return;
                    }

                    try {
                        const res = await fetch(`/admin/pos/recall/${held.id}`, {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                "Accept": "application/json"
                            }
                        });
                        
                        const data = await res.json();
                        if (res.ok) {
                            this.cart = data.data.cart_data;
                            this.customer = data.data.customer_data || {name:'', phone:''};
                            this.isHeldOrdersModalOpen = false;
                            this.fetchHeldOrders();
                        }
                    } catch(e) { alert('Network Error.'); }
                },

                async deleteHeldOrder(id) {
                    if(!confirm('Discard this held order?')) return;
                    try {
                        const res = await fetch(`/admin/pos/held/${id}`, {
                            method: "DELETE",
                            headers: {
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                "Accept": "application/json"
                            }
                        });
                        if (res.ok) this.fetchHeldOrders();
                    } catch(e) {}
                },

                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(err => {
                            console.log(`Error attempting to enable fullscreen: ${err.message}`);
                        });
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        }
                    }
                },

                playBeep(freq = 800) {
                    if (!window.AudioContext) return;
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, ctx.currentTime);
                    gain.gain.setValueAtTime(0.05, ctx.currentTime);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.05);
                },

                playSuccessBeep() {
                    if (!window.AudioContext) return;
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    
                    const playNote = (freq, startTime, duration) => {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.frequency.setValueAtTime(freq, ctx.currentTime + startTime);
                        gain.gain.setValueAtTime(0.1, ctx.currentTime + startTime);
                        gain.gain.setTargetAtTime(0, ctx.currentTime + startTime + duration - 0.05, 0.05);
                        osc.start(ctx.currentTime + startTime);
                        osc.stop(ctx.currentTime + startTime + duration);
                    };

                    playNote(523.25, 0, 0.1); // C5
                    playNote(659.25, 0.1, 0.1); // E5
                    playNote(783.99, 0.2, 0.2); // G5
                }
            }
        }

        document.addEventListener('keydown', (e) => {
            const system = document.querySelector('[x-data="posSystem()"]').__x.$data;
            if(!system || system.isPaymentModalOpen || system.isHoldModalOpen || system.isHeldOrdersModalOpen) return;

            if (e.key === 'F8') {
                e.preventDefault();
                if(system.cart.length > 0) { system.paymentMethod = 'cash'; system.openPaymentModal(); }
            }
            if (e.key === 'F9') {
                e.preventDefault();
                if(system.cart.length > 0) system.processCardPayment();
            }
        });
    </script>
</body>
</html>
