<section class="relative min-h-[70vh] flex items-center bg-surface-900 overflow-hidden">
    {{-- High-End Animated Background --}}
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-600/20 via-indigo-900/40 to-black"></div>
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-primary-500/10 blur-[120px] rounded-full animate-pulse"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] bg-indigo-500/10 blur-[150px] rounded-full animate-pulse" style="animation-delay: 2s;"></div>
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-[0.03] mix-blend-overlay"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-8 py-24 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10 text-primary-400 text-[10px] font-black uppercase tracking-[0.2em] mb-8 backdrop-blur-md animate-fade-in">
            <span class="w-1.5 h-1.5 rounded-full bg-primary-500 animate-ping"></span>
            {{ $store->name }} Visionary Edge
        </div>
        
        <h1 class="text-5xl lg:text-8xl font-display font-black text-white leading-[1.1] tracking-tighter mb-8 animate-slide-up">
            {{ $section->getContent('title', 'Crafting Digital Excellence') }}
        </h1>
        
        <p class="text-lg lg:text-xl text-surface-400 max-w-3xl mx-auto leading-relaxed mb-12 animate-slide-up" style="animation-delay: 0.1s;">
            {{ $section->getContent('subtitle', 'Experience the next generation of e-commerce architecture, designed for scale and built for growth.') }}
        </p>

        @if($section->getContent('button_text'))
        <div class="flex flex-col sm:flex-row items-center justify-center gap-6 animate-slide-up" style="animation-delay: 0.2s;">
            <a href="{{ $section->getContent('button_url', '#') }}" class="group relative px-10 py-5 bg-primary-600 hover:bg-primary-500 text-white font-black rounded-[2rem] transition-all shadow-[0_20px_50px_rgba(79,70,229,0.3)] hover:shadow-primary-500/50 hover:-translate-y-1">
                <span class="relative z-10 flex items-center gap-3">
                    {{ $section->getContent('button_text') }}
                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </span>
            </a>
            <a href="#" class="px-10 py-5 bg-white/5 hover:bg-white/10 text-white font-bold rounded-[2rem] border border-white/10 transition-all backdrop-blur-xl">
                Explore Features
            </a>
        </div>
        @endif
    </div>
</section>
