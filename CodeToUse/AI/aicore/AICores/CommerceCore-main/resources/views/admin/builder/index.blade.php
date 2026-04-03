<x-layouts.admin>
    <x-slot:header>Page Builder</x-slot:header>

    <div class="space-y-8">
        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-display font-bold text-surface-900 dark:text-white tracking-tight">Site Architect</h1>
                <p class="text-surface-500 dark:text-surface-400 mt-1 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
                    Design and manage your store's visual identity
                </p>
            </div>
            <a href="{{ route('admin.builder.create') }}" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-2xl shadow-lg shadow-primary-500/20 transition-all active:scale-95 group">
                <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Deploy New Page
            </a>
        </div>

        {{-- Pages Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse($pages as $page)
            <div class="group relative bg-white dark:bg-surface-800/50 backdrop-blur-xl border border-surface-200 dark:border-surface-700/50 rounded-3xl overflow-hidden hover:shadow-2xl hover:shadow-primary-500/10 transition-all duration-500">
                {{-- Status Glow Indicator --}}
                <div class="absolute top-0 left-0 w-full h-1 {{ $page->is_published ? 'bg-emerald-500' : 'bg-amber-500' }} shadow-[0_0_15px_rgba(16,185,129,0.3)] opacity-0 group-hover:opacity-100 transition-opacity"></div>

                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 bg-surface-50 dark:bg-surface-700/50 rounded-2xl group-hover:scale-110 transition-transform duration-500">
                            @if($page->is_homepage)
                                <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            @else
                                <svg class="w-6 h-6 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            @endif
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest {{ $page->is_published ? 'bg-emerald-500/10 text-emerald-600' : 'bg-amber-500/10 text-amber-600' }}">
                                {{ $page->is_published ? 'Live' : 'Draft' }}
                            </span>
                        </div>
                    </div>

                    <h3 class="text-xl font-display font-bold text-surface-900 dark:text-white group-hover:text-primary-500 transition-colors">{{ $page->page_name }}</h3>
                    <p class="text-sm text-surface-400 mt-1 font-mono tracking-tighter">/{{ $page->slug }}</p>

                    <div class="flex items-center gap-6 mt-6">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-primary-500"></div>
                            <span class="text-xs font-bold text-surface-500 dark:text-surface-400">{{ $page->sections_count ?? 0 }} Blocks</span>
                        </div>
                        <div class="flex items-center gap-2 text-surface-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-[11px] font-medium">{{ $page->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex border-t border-surface-200/60 dark:border-surface-700/50 divide-x divide-surface-200/60 dark:divide-surface-700/50">
                    <a href="{{ route('admin.builder.edit', $page) }}" class="flex-1 flex items-center justify-center gap-2 px-4 py-4 text-sm font-bold text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all group/btn">
                        <svg class="w-4 h-4 group-hover/btn:scale-125 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Canvas
                    </a>
                    <a href="{{ route('admin.builder.preview', $page) }}" target="_blank" class="flex-1 flex items-center justify-center gap-2 px-4 py-4 text-sm font-bold text-surface-600 dark:text-surface-300 hover:bg-surface-50 dark:hover:bg-surface-700/50 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Live View
                    </a>
                    <form method="POST" action="{{ route('admin.builder.delete', $page) }}" onsubmit="return confirm('Archive this architect file?')" class="flex">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-5 py-4 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="col-span-full py-20 bg-surface-50 dark:bg-surface-800/20 border-2 border-dashed border-surface-200 dark:border-surface-700 rounded-[2rem] flex flex-col items-center justify-center text-center">
                <div class="w-20 h-20 bg-white dark:bg-surface-800 rounded-3xl shadow-xl flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-surface-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                </div>
                <h3 class="text-xl font-display font-bold text-surface-900 dark:text-white">Design Your Storefront</h3>
                <p class="text-surface-500 max-w-sm mt-2">Start by creating your first page and choosing from our premium design blocks.</p>
                <a href="{{ route('admin.builder.create') }}" class="mt-8 px-8 py-3 bg-primary-600 text-white font-bold rounded-2xl shadow-xl shadow-primary-500/20 hover:scale-105 transition-transform">Initialize First Page</a>
            @endforelse
        </div>
    </div>
</x-layouts.admin>
