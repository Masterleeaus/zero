<section class="py-24 px-6">
    <div class="max-w-7xl mx-auto bg-surface-900 rounded-[4rem] p-12 lg:p-24 relative overflow-hidden group">
        {{-- Glow Orbs --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-primary-500/20 blur-[120px] rounded-full group-hover:bg-primary-500/30 transition-colors duration-700"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-indigo-500/10 blur-[100px] rounded-full"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-12">
            <div class="max-w-2xl text-center lg:text-left">
                <h2 class="text-4xl lg:text-6xl font-display font-black text-white leading-tight tracking-tighter">
                    {{ $section->getContent('title', 'Ready to Scale?') }}
                </h2>
                <p class="mt-6 text-xl text-surface-400 leading-relaxed">
                    {{ $section->getContent('subtitle', 'Join thousands of high-growth brands building their future on our architecture.') }}
                </p>
            </div>
            
            <div class="flex-shrink-0">
                <a href="{{ $section->getContent('button_url', '#') }}" class="inline-flex items-center gap-4 px-12 py-6 bg-primary-600 hover:bg-primary-500 text-white text-lg font-black rounded-3xl transition-all shadow-[0_20px_50px_rgba(79,70,229,0.3)] hover:shadow-primary-500/50 hover:-translate-y-1">
                    {{ $section->getContent('button_text', 'Get Started Now') }}
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>
