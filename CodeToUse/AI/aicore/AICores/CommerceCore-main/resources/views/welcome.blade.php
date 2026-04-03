<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CommerceCore | SaaS Multi-Tenant ERP & Ecommerce</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            body { font-family: 'Inter', sans-serif; }
            h1, h2, h3 { font-family: 'Outfit', sans-serif; }
            .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); }
            .glass-border { border: 1px solid rgba(255, 255, 255, 0.1); }
            .bg-gradient { background: radial-gradient(circle at 0% 0%, #312e81 0%, #1e1b4b 100%); }
            .animate-float { animation: float 6s ease-in-out infinite; }
            @keyframes float { 0% { transform: translateY(0px); } 50% { transform: translateY(-20px); } 100% { transform: translateY(0px); } }
        </style>
    </head>
    <body class="bg-[#0b0a22] text-white min-h-screen selection:bg-violet-500 selection:text-white">
        <!-- Navigation -->
        <nav class="fixed top-0 left-0 right-0 z-50 glass border-b border-white/5 py-4">
            <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-violet-500/20">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight text-white">CommerceCore<span class="text-violet-500">+</span></span>
                </div>
                
                <div class="flex items-center gap-6">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/admin') }}" class="text-sm font-semibold hover:text-violet-400 transition-colors uppercase tracking-widest text-violet-500">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-semibold hover:text-violet-400 transition-colors uppercase tracking-widest">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="bg-violet-600 hover:bg-violet-700 px-6 py-2 rounded-full text-sm font-bold transition-all shadow-lg shadow-violet-600/20">Get Started</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="relative pt-32 pb-20 px-6 overflow-hidden">
            <!-- Background Orbs -->
            <div class="absolute top-0 -left-20 w-96 h-96 bg-violet-600/20 rounded-full blur-[100px] pointer-events-none"></div>
            <div class="absolute bottom-0 -right-20 w-96 h-96 bg-indigo-600/20 rounded-full blur-[100px] pointer-events-none"></div>

            <div class="max-w-7xl mx-auto flex flex-col items-center text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-500/10 border border-violet-500/20 text-violet-400 text-xs font-bold mb-8 uppercase tracking-widest">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-violet-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-violet-500"></span>
                    </span>
                    The Ultimate SaaS Built For Scale
                </div>

                <h1 class="text-6xl md:text-8xl font-extrabold tracking-tight mb-8 leading-[1.1]">
                    The Intelligent <br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-400 via-indigo-400 to-indigo-500">ERP & Ecommerce</span>
                </h1>

                <p class="max-w-2xl text-lg md:text-xl text-slate-400 font-medium mb-12">
                    A multi-tenant ecosystem unifying Storefront design, DeepBack-Office Logistics, and AI Intelligence. One platform to rule your entire business galaxy.
                </p>

                <div class="flex flex-col sm:flex-row items-center gap-4">
                    <a href="{{ route('admin.dashboard') }}" class="w-full sm:w-auto px-8 py-4 bg-violet-600 hover:bg-violet-700 text-white rounded-2xl font-bold text-lg shadow-xl shadow-violet-600/30 transition-all hover:-translate-y-1 flex items-center justify-center gap-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                        Admin Dashboard
                    </a>
                    <a href="/store/techvault" class="w-full sm:w-auto px-8 py-4 glass text-white rounded-2xl font-bold text-lg border border-white/10 hover:bg-white/5 transition-all flex items-center justify-center gap-3 group">
                        <svg class="w-6 h-6 text-violet-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        Explore Storefront
                    </a>
                </div>

                <!-- Dashboard Preview Simulation -->
                <div class="mt-24 w-full max-w-5xl glass rounded-[2.5rem] p-4 glass-border shadow-2xl relative animate-float">
                    <div class="absolute -top-12 -left-12 w-24 h-24 bg-violet-500/40 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-12 -right-12 w-24 h-24 bg-indigo-500/40 rounded-full blur-3xl"></div>
                    
                    <div class="bg-[#0f0d2c]/80 rounded-[2rem] overflow-hidden aspect-[16/9] flex items-center justify-center p-12 border border-white/5">
                        <div class="text-center">
                            <div class="flex items-center justify-center gap-8 mb-8">
                                <div class="w-32 h-32 rounded-3xl bg-violet-500/20 border border-violet-500/30 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                </div>
                                <div class="w-32 h-32 rounded-3xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                </div>
                                <div class="w-32 h-32 rounded-3xl bg-pink-500/20 border border-pink-500/30 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                            </div>
                            <h3 class="text-2xl font-bold mb-2">CommerceCore Mission Control</h3>
                            <p class="text-slate-500">Intelligent ERP, Predictive Inventory & AI Forensic Insights</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Features Grid -->
        <section class="max-w-7xl mx-auto px-6 py-20 border-t border-white/5">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="p-8 glass rounded-3xl border border-white/5 hover:border-violet-500/30 transition-all group">
                    <div class="w-12 h-12 bg-violet-600/10 rounded-2xl flex items-center justify-center text-violet-500 mb-6 font-black text-xl group-hover:scale-110 transition-transform">01</div>
                    <h3 class="text-xl font-bold mb-4">Multi-Tenant SaaS</h3>
                    <p class="text-slate-400 leading-relaxed text-sm">Industrial-grade isolation for thousands of stores. Domain resolution & separate billing ecosystem.</p>
                </div>
                <div class="p-8 glass rounded-3xl border border-white/5 hover:border-indigo-500/30 transition-all group">
                    <div class="w-12 h-12 bg-indigo-600/10 rounded-2xl flex items-center justify-center text-indigo-500 mb-6 font-black text-xl group-hover:scale-110 transition-transform">02</div>
                    <h3 class="text-xl font-bold mb-4">Unified ERP Power</h3>
                    <p class="text-slate-400 leading-relaxed text-sm">Real-time sync between POS, Inventory, and Accounting. Zero silos across your business galaxy.</p>
                </div>
                <div class="p-8 glass rounded-3xl border border-white/5 hover:border-pink-500/30 transition-all group">
                    <div class="w-12 h-12 bg-pink-600/10 rounded-2xl flex items-center justify-center text-pink-500 mb-6 font-black text-xl group-hover:scale-110 transition-transform">03</div>
                    <h3 class="text-xl font-bold mb-4">AI Forensic Intelligence</h3>
                    <p class="text-slate-400 leading-relaxed text-sm">Dogwatch AI monitors risk, while NLP allows you to chat directly with your sales and ledger data.</p>
                </div>
                <div class="p-8 glass rounded-3xl border border-white/5 border-violet-500/40 bg-violet-500/5 transition-all group relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-violet-500/20 blur-xl rounded-full"></div>
                    <div class="w-12 h-12 bg-violet-500 rounded-2xl flex items-center justify-center text-white mb-6 font-black text-xl shadow-lg shadow-violet-500/20 group-hover:rotate-12 transition-transform">04</div>
                    <h3 class="text-xl font-bold mb-4 text-white">Visual Storefront</h3>
                    <p class="text-slate-400 leading-relaxed text-sm italic">"Complete freedom with Site Architect. Support for Custom HTML, CSS, and JS blocks."</p>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-12 border-t border-white/5 text-center text-slate-500 text-sm">
            &copy; {{ date('Y') }} <span class="text-white font-bold ml-1">CommerceCore<span class="text-violet-500">+</span></span>. All rights reserved. Built for Scaling Giants.
        </footer>
    </body>
</html>
