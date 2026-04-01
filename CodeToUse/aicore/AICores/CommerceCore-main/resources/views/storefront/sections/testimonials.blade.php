<section class="py-32 px-6 bg-surface-900 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-primary-500/10 blur-[150px] rounded-full"></div>
    <div class="absolute bottom-0 left-0 w-[300px] h-[300px] bg-indigo-500/10 blur-[100px] rounded-full"></div>

    <div class="relative z-10 max-w-7xl mx-auto">
        <div class="text-center max-w-3xl mx-auto mb-20">
            <span class="text-primary-400 text-[10px] font-black uppercase tracking-[0.3em] mb-4 block">Social Proof</span>
            <h2 class="text-4xl lg:text-5xl font-display font-black text-white tracking-tighter mb-6">
                {{ $section->getContent('title', 'Trusted by Visionaries') }}
            </h2>
            <p class="text-lg text-surface-400">
                {{ $section->getContent('subtitle', 'Join the global community of architects and brands building on our platform.') }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @for($i = 1; $i <= 3; $i++)
            @if($section->getContent("review_{$i}"))
            <div class="group p-10 bg-white/5 backdrop-blur-xl border border-white/10 rounded-[3rem] hover:bg-white/10 transition-all duration-500 hover:-translate-y-2">
                <div class="flex gap-1 mb-8">
                    @for($j = 0; $j < 5; $j++)
                        <svg class="w-4 h-4 text-amber-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                
                <p class="text-lg text-surface-300 leading-relaxed mb-10 italic">
                    "{{ $section->getContent("review_{$i}") }}"
                </p>

                <div class="flex items-center gap-5 mt-auto pt-8 border-t border-white/5">
                    <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-sm shadow-xl shadow-primary-500/20">
                        {{ substr($section->getContent("author_{$i}"), 0, 1) }}
                    </div>
                    <div>
                        <h4 class="text-white font-bold">{{ $section->getContent("author_{$i}") }}</h4>
                        <p class="text-primary-400 text-[10px] font-black uppercase tracking-widest mt-1">Verified Architect</p>
                    </div>
                </div>
            </div>
            @endif
            @endfor
        </div>
    </div>
</section>
