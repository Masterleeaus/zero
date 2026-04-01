<section class="py-32 px-6 bg-white dark:bg-surface-950 overflow-hidden relative">
    {{-- Decorative Background --}}
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-[500px] bg-gradient-to-b from-primary-500/5 to-transparent blur-3xl opacity-50"></div>
    
    <div class="relative max-w-7xl mx-auto">
        <div class="text-center max-w-3xl mx-auto mb-24">
            <span class="text-primary-500 text-[10px] font-black uppercase tracking-[0.3em] mb-4 block">Core Infrastructure</span>
            <h2 class="text-4xl lg:text-5xl font-display font-black text-surface-900 dark:text-white tracking-tighter mb-6">
                {{ $section->getContent('title', 'Engineered for Performance') }}
            </h2>
            <p class="text-lg text-surface-500 leading-relaxed">
                {{ $section->getContent('subtitle', 'Every component is precision-crafted to deliver an unparalleled digital experience.') }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            @for($i = 1; $i <= 3; $i++)
            <div class="relative group">
                <div class="absolute -inset-4 bg-gradient-to-br from-primary-500/10 to-transparent rounded-[3rem] opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                <div class="relative p-10 bg-surface-50 dark:bg-surface-900 rounded-[2.5rem] border border-surface-100 dark:border-surface-800 transition-all duration-500 group-hover:scale-[1.02] group-hover:shadow-2xl group-hover:shadow-primary-500/5">
                    <div class="w-16 h-16 rounded-2xl bg-white dark:bg-surface-800 shadow-xl shadow-black/5 flex items-center justify-center mb-8 text-primary-500 group-hover:rotate-6 group-hover:scale-110 transition-transform duration-500">
                        @if($i == 1)
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        @elseif($i == 2)
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        @else
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                        @endif
                    </div>
                    <h3 class="text-2xl font-bold text-surface-900 dark:text-white mb-4">{{ $section->getContent("feature_{$i}_title") }}</h3>
                    <p class="text-surface-500 leading-relaxed">{{ $section->getContent("feature_{$i}_desc") }}</p>
                </div>
            </div>
            @endfor
        </div>
    </div>
</section>
