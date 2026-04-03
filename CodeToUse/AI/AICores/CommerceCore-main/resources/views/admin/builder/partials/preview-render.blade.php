@if($section->type === 'hero')
<div class="bg-gradient-to-r from-primary-600 to-indigo-800 rounded-3xl p-12 text-white text-center shadow-inner relative overflow-hidden group/hero">
    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10"></div>
    <div class="relative z-10">
        <h2 class="text-4xl font-display font-black tracking-tight leading-tight">{{ $section->getContent('title') }}</h2>
        <p class="text-primary-100 mt-4 text-lg max-w-2xl mx-auto leading-relaxed">{{ $section->getContent('subtitle') }}</p>
        <div class="mt-10 flex items-center justify-center gap-4">
            <span class="px-8 py-3.5 bg-white text-primary-600 rounded-2xl text-sm font-black shadow-xl shadow-black/10">{{ $section->getContent('button_text') }}</span>
            <span class="px-8 py-3.5 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl text-sm font-bold text-white">Learn More</span>
        </div>
    </div>
</div>

@elseif($section->type === 'stats')
<div class="grid grid-cols-2 md:grid-cols-4 gap-6">
    @for($i = 1; $i <= 4; $i++)
    <div class="bg-white dark:bg-surface-800/60 p-6 rounded-3xl border border-surface-200 dark:border-surface-700/50 text-center">
        <p class="text-[10px] font-black text-surface-400 uppercase tracking-widest">{{ $section->getContent("stat_{$i}_label") }}</p>
        <p class="text-3xl font-display font-black text-primary-500 mt-1">{{ $section->getContent("stat_{$i}_value") }}</p>
    </div>
    @endfor
</div>

@elseif($section->type === 'product_grid')
<div class="relative">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h3 class="text-2xl font-display font-bold text-surface-900 dark:text-white">{{ $section->getContent('title') }}</h3>
            <p class="text-sm text-surface-500 mt-1">{{ $section->getContent('subtitle') }}</p>
        </div>
        <span class="text-xs font-black text-primary-500 uppercase tracking-widest">View Collection →</span>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        @for($i = 1; $i <= 4; $i++)
        <div class="space-y-3 group/prod">
            <div class="aspect-square bg-surface-100 dark:bg-surface-800 rounded-[2rem] border border-surface-200 dark:border-surface-700/50 overflow-hidden relative">
                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                <div class="absolute bottom-4 left-4 right-4 flex justify-between items-center opacity-0 group-hover/prod:opacity-100 transition-opacity">
                    <div class="w-8 h-8 bg-white rounded-xl shadow-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-surface-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                </div>
            </div>
            <div>
                <div class="h-4 bg-surface-100 dark:bg-surface-800 rounded-lg w-3/4 mb-2"></div>
                <div class="h-3 bg-surface-50 dark:bg-surface-900 rounded-lg w-1/2"></div>
            </div>
        </div>
        @endfor
    </div>
</div>

@elseif($section->type === 'cta')
<div class="bg-surface-900 rounded-[3rem] p-12 text-white flex flex-col md:flex-row items-center justify-between gap-8 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-64 h-64 bg-primary-500/20 blur-[100px] rounded-full"></div>
    <div class="relative z-10 max-w-xl">
        <h3 class="text-3xl font-display font-bold leading-tight">{{ $section->getContent('title') }}</h3>
        <p class="text-surface-400 mt-3 text-lg">{{ $section->getContent('subtitle') }}</p>
    </div>
    <button class="relative z-10 px-10 py-4 bg-primary-600 hover:bg-primary-500 text-white font-black rounded-2xl transition-all shadow-2xl shadow-primary-500/40 whitespace-nowrap">
        {{ $section->getContent('button_text') }}
    </button>
</div>

@elseif($section->type === 'features')
<div class="text-center mb-12">
    <h3 class="text-3xl font-display font-bold text-surface-900 dark:text-white">{{ $section->getContent('title') }}</h3>
    <p class="text-surface-500 mt-2">{{ $section->getContent('subtitle') }}</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    @for($i = 1; $i <= 3; $i++)
    <div class="p-8 bg-white dark:bg-surface-800/40 rounded-[2.5rem] border border-surface-100 dark:border-surface-700/50 hover:shadow-xl transition-all">
        <div class="w-14 h-14 bg-primary-50 dark:bg-primary-900/20 rounded-2xl flex items-center justify-center mb-6 text-primary-600">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <h4 class="text-lg font-bold text-surface-900 dark:text-white mb-3">{{ $section->getContent("feature_{$i}_title") }}</h4>
        <p class="text-sm text-surface-500 leading-relaxed">{{ $section->getContent("feature_{$i}_desc") }}</p>
    </div>
    @endfor
</div>

@elseif($section->type === 'testimonials')
<div class="bg-surface-50 dark:bg-surface-900/50 rounded-[3rem] p-12">
    <h3 class="text-2xl font-display font-bold text-surface-900 dark:text-white text-center mb-10">{{ $section->getContent('title') }}</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @for($i = 1; $i <= 2; $i++)
        <div class="bg-white dark:bg-surface-800 p-8 rounded-[2rem] shadow-sm relative">
            <svg class="absolute top-6 right-8 w-10 h-10 text-primary-500/10" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21L14.017 18C14.017 16.8954 14.9124 16 16.017 16H19.017C19.5693 16 20.017 15.5523 20.017 15V9C20.017 8.44772 19.5693 8 19.017 8H16.017C14.9124 8 14.017 7.10457 14.017 6V4L21.017 4V15C21.017 18.3137 18.3307 21 15.017 21H14.017ZM3.01693 21L3.01693 18C3.01693 16.8954 3.91236 16 5.01693 16H8.01693C8.56921 16 9.01693 15.5523 9.01693 15V9C9.01693 8.44772 8.56921 8 8.01693 8H5.01693C3.91236 8 3.01693 7.10457 3.01693 6V4L10.0169 4V15C10.0169 18.3137 7.3306 21 4.01693 21H3.01693Z"/></svg>
            <p class="text-lg text-surface-700 dark:text-surface-300 italic mb-6">"{{ $section->getContent("review_{$i}") }}"</p>
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-primary-500 rounded-full flex items-center justify-center text-white font-bold text-xs">
                    {{ substr($section->getContent("author_{$i}"), 0, 1) }}
                </div>
                <div>
                    <h5 class="text-sm font-bold text-surface-900 dark:text-white">{{ $section->getContent("author_{$i}") }}</h5>
                    <p class="text-xs text-primary-500 font-medium">Verified Architect</p>
                </div>
            </div>
        </div>
        @endfor
    </div>
</div>

@elseif($section->type === 'custom_code')
<div class="p-12 bg-surface-900 border border-violet-500/30 rounded-[3rem] text-center relative overflow-hidden group/code">
    <div class="absolute inset-0 bg-violet-500/5 backdrop-blur-3xl"></div>
    <div class="relative z-10">
        <div class="w-16 h-16 bg-violet-500 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-violet-500/20 group-hover/code:rotate-12 transition-transform">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
        </div>
        <h4 class="text-xl font-bold text-white mb-2">Custom Code Block Deployed</h4>
        <p class="text-surface-400 text-sm max-w-sm mx-auto mb-6">HTML, CSS, and JS injection active. View in live storefront to see full rendering.</p>
        
        <button @click="editing = true" class="px-6 py-2 bg-violet-500/20 hover:bg-violet-500/40 border border-violet-500/50 rounded-xl text-xs font-bold text-violet-300 transition-all">
            Click to Configure Source
        </button>
        
        <div class="mt-8 flex items-center justify-center gap-2">
            <span class="px-3 py-1 bg-white/5 border border-white/10 rounded-lg text-[10px] font-black text-violet-400 uppercase tracking-widest">HTML5</span>
            <span class="px-3 py-1 bg-white/5 border border-white/10 rounded-lg text-[10px] font-black text-blue-400 uppercase tracking-widest">CSS3</span>
            <span class="px-3 py-1 bg-white/5 border border-white/10 rounded-lg text-[10px] font-black text-amber-400 uppercase tracking-widest">ES6+</span>
        </div>
    </div>
</div>

@else
{{-- Fallback for basic text/etc --}}
<div class="p-4 border-2 border-dashed border-surface-200 dark:border-surface-700 rounded-2xl text-center">
    <p class="text-sm text-surface-400">Architect View for <strong>{{ $section->type }}</strong> deployed successfully.</p>
</div>
@endif
