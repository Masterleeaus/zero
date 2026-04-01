<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    @php
        $settings = $store->settings;
        $primaryColor = $settings?->getSetting('primary_color', '#4F46E5') ?? '#4F46E5';
        $favicon = $settings?->getSetting('favicon') ? asset('storage/' . $settings->getSetting('favicon')) : asset('images/favicon.png');
        $ecomLogo = $settings?->getSetting('ecom_logo') ? asset('storage/' . $settings->getSetting('ecom_logo')) : ($store->logo ? asset('storage/' . $store->logo) : null);
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $store->name }} — CommerceCore</title>
    <link rel="icon" type="image/png" href="{{ $favicon }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800|outfit:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --store-primary: {{ $primaryColor }}; }
    </style>

    {{-- Facebook Pixel --}}
    @if($store->facebook_pixel_id)
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '{{ $store->facebook_pixel_id }}');
    fbq('track', 'PageView');
    </script>
    <noscript>
    <img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id={{ $store->facebook_pixel_id }}&ev=PageView&noscript=1"/>
    </noscript>
    @endif
</head>
<body class="font-sans antialiased bg-white dark:bg-surface-900 text-surface-800 dark:text-surface-200">

    {{-- Toast --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
         x-transition class="fixed top-4 right-4 z-50 px-5 py-3 bg-emerald-500 text-white rounded-xl shadow-lg font-medium text-sm">
        {{ session('success') }}
    </div>
    @endif

    {{-- Navbar --}}
    <nav class="sticky top-0 z-40 bg-white/80 dark:bg-surface-900/80 backdrop-blur-xl border-b border-surface-200/50 dark:border-surface-700/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('storefront.home', $store->slug) }}" class="flex items-center gap-3">
                    @if($ecomLogo)
                    <img src="{{ $ecomLogo }}" alt="{{ $store->name }}" class="h-8 w-8 rounded-lg object-contain">
                    @else
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[var(--store-primary)] to-primary-700 flex items-center justify-center text-white font-display font-bold shadow-sm shadow-[var(--store-primary)]/40">{{ strtoupper(substr($store->name, 0, 1)) }}</div>
                    @endif
                    <span class="font-display font-bold text-lg text-surface-800 dark:text-white">{{ $store->name }}</span>
                </a>

                <div class="flex items-center gap-6">
                    <a href="{{ route('storefront.products', $store->slug) }}" class="text-sm font-medium text-surface-600 dark:text-surface-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Products</a>
                    <a href="{{ route('storefront.cart', $store->slug) }}" class="relative p-2 text-surface-600 dark:text-surface-300 hover:text-primary-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        @php $cartCount = app(App\Services\CartService::class)->getCount($store->slug); @endphp
                        @if($cartCount > 0)
                        <span class="absolute -top-1 -right-1 w-5 h-5 bg-primary-500 text-white text-xs font-bold rounded-full flex items-center justify-center">{{ $cartCount }}</span>
                        @endif
                    </a>
                    <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="p-2 text-surface-500 hover:text-primary-600 transition-colors">
                        <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </button>

                    {{-- Currency Switcher --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-surface-50 dark:bg-surface-800 text-xs font-bold text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-700 transition-all border border-surface-200/50 dark:border-surface-700/50">
                            <span>{{ $currency->getUserCurrency() }}</span>
                            <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition
                             class="absolute right-0 mt-2 w-48 bg-white dark:bg-surface-800 rounded-2xl shadow-2xl border border-surface-200 dark:border-surface-700 overflow-hidden z-50">
                            <div class="p-2 grid grid-cols-1 gap-1">
                                @foreach($currency->getSupported() as $code => $info)
                                <a href="?currency={{ $code }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-[11px] font-bold {{ $currency->getUserCurrency() === $code ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400' : 'text-surface-600 dark:text-surface-400 hover:bg-surface-50 dark:hover:bg-surface-700/50' }} transition-colors">
                                    <span>{{ $info['name'] }}</span>
                                    <span class="opacity-50 tracking-widest">{{ $code }}</span>
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- AI Voice Search Trigger --}}
                    <div x-data="voiceSearch('{{ $store->slug }}')" class="relative">
                        <button @click="toggle()" 
                                :class="recording ? 'bg-rose-500 text-white animate-pulse' : 'text-surface-500 hover:text-primary-600'"
                                class="p-2 rounded-xl transition-all duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                        </button>

                        {{-- Floating Results --}}
                        <div x-show="open" @click.away="open = false" x-transition
                             class="absolute right-0 mt-4 w-80 sm:w-96 bg-white dark:bg-surface-800 rounded-3xl shadow-2xl border border-surface-200 dark:border-surface-700 overflow-hidden z-50">
                            <div class="p-6 bg-gradient-to-br from-primary-50 to-indigo-50 dark:from-surface-900/50 dark:to-surface-800/50 border-b border-surface-100 dark:border-surface-700 relative overflow-hidden">
                                <div class="absolute inset-0 bg-white/40 dark:bg-transparent pointer-events-none"></div>
                                <div class="relative z-10 flex items-center justify-between">
                                    <h3 class="text-xs font-black uppercase text-primary-600 dark:text-primary-400 tracking-widest italic">Neural Voice Search</h3>
                                    <template x-if="recording">
                                        <div class="flex gap-1">
                                            <div class="w-1 h-3 bg-rose-500 animate-bounce"></div>
                                            <div class="w-1 h-3 bg-rose-500 animate-bounce" style="animation-delay: 0.1s"></div>
                                            <div class="w-1 h-3 bg-rose-500 animate-bounce" style="animation-delay: 0.2s"></div>
                                        </div>
                                    </template>
                                </div>
                                <p class="mt-4 text-lg font-display font-medium text-surface-800 dark:text-white leading-tight italic" x-text="transcript || 'Listening for keywords...'"></p>
                            </div>

                            <div class="max-h-[350px] overflow-y-auto p-2 scrollbar-hide">
                                <template x-if="processing">
                                    <div class="py-12 text-center">
                                        <div class="w-8 h-8 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                                        <p class="text-[10px] font-black text-surface-400 uppercase tracking-widest">Processing Intent...</p>
                                    </div>
                                </template>

                                <template x-for="product in results" :key="product.id">
                                    <a :href="`/store/${storeSlug}/product/${product.slug}`" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-surface-50 dark:hover:bg-surface-700/50 transition-all group">
                                        <div class="w-16 h-16 rounded-xl bg-surface-100 dark:bg-surface-700 overflow-hidden border border-surface-200/50 dark:border-surface-600/50">
                                            <img :src="product.image ? `/storage/${product.image}` : '/placeholder.jpg'" class="w-full h-full object-cover">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-sm font-bold text-surface-800 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors" x-text="product.name"></h4>
                                            <p class="text-[10px] font-black text-indigo-500 mt-1">$<template x-text="parseFloat(product.price).toFixed(2)"></template></p>
                                        </div>
                                        <div class="p-2 opacity-0 group-hover:opacity-100 transition-all translate-x-4 group-hover:translate-x-0">
                                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </div>
                                    </a>
                                </template>

                                <template x-if="!results.length && !processing && transcript">
                                    <div class="py-12 text-center">
                                        <p class="text-[10px] font-black text-rose-400 uppercase tracking-widest italic">No matches found. Try another request.</p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-surface-50 dark:bg-surface-800 border-t border-surface-200 dark:border-surface-700 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-sm">{{ strtoupper(substr($store->name, 0, 1)) }}</div>
                    <span class="font-display font-semibold text-surface-800 dark:text-white">{{ $store->name }}</span>
                </div>
                <p class="text-sm text-surface-400">© {{ date('Y') }} {{ $store->name }}. Powered by <span class="gradient-text font-semibold">CommerceCore</span></p>
            </div>
        </div>
    </footer>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('voiceSearch', (storeSlug) => ({
                storeSlug: storeSlug,
                open: false,
                recording: false,
                processing: false,
                transcript: '',
                results: [],
                recognition: null,

                init() {
                    if ('webkitSpeechRecognition' in window) {
                        this.recognition = new webkitSpeechRecognition();
                        this.recognition.continuous = false;
                        this.recognition.lang = 'en-US';
                        this.recognition.onresult = (event) => {
                            this.transcript = event.results[0][0].transcript;
                            this.stop();
                            this.executeSearch();
                        };
                        this.recognition.onend = () => { this.recording = false; };
                    }
                },
                toggle() {
                    if (!this.recognition) return alert('Speech recognition not supported in your browser.');
                    this.recording ? this.stop() : this.start();
                },
                start() {
                    this.open = true;
                    this.recording = true;
                    this.transcript = '';
                    this.results = [];
                    this.recognition.start();
                },
                stop() {
                    this.recording = false;
                    this.recognition.stop();
                },
                async executeSearch() {
                    this.processing = true;
                    try {
                        const response = await fetch(`/store/${this.storeSlug}/voice-search?transcript=${encodeURIComponent(this.transcript)}`);
                        const data = await response.json();
                        this.results = data.products;
                    } catch (e) {
                        console.error('Search failed', e);
                    }
                    this.processing = false;
                }
            }));

            // Flash Pixel Events
            @if(session('pixel_event'))
                @php $pixel = session('pixel_event'); @endphp
                if (window.fbq) {
                    fbq('track', '{{ $pixel['name'] }}', {!! json_encode($pixel['data']) !!});
                }
            @endif
        });
    </script>
</body>
</html>
