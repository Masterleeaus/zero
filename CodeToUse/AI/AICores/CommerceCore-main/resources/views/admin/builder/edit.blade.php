<x-layouts.admin>
    <x-slot:header>Architect — {{ $page->page_name }}</x-slot:header>

    <div class="relative min-h-[calc(100vh-12rem)] pb-24" x-data="{ activeCategory: 'all', activeDevice: 'desktop' }">
        {{-- Architecture Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- Library Sidebar (Left) --}}
            <aside class="lg:col-span-3">
                <div class="sticky top-6 space-y-4">
                    <div class="bg-white dark:bg-surface-800/40 backdrop-blur-xl rounded-[2rem] border border-surface-200 dark:border-surface-700/50 overflow-hidden shadow-xl shadow-surface-200/20 dark:shadow-none">
                        <div class="px-6 py-5 border-b border-surface-100 dark:border-surface-700/50">
                            <h3 class="text-xs font-black text-primary-500 uppercase tracking-widest">Component Library</h3>
                            <p class="text-[10px] text-surface-400 mt-1 uppercase">Drag or click to deploy</p>
                        </div>
                        
                        {{-- Category Tabs --}}
                        <div class="p-2 flex gap-1 bg-surface-50/50 dark:bg-surface-900/30">
                            <button @click="activeCategory = 'all'" :class="activeCategory === 'all' ? 'bg-white dark:bg-surface-700 text-primary-600 shadow-sm' : 'text-surface-400 hover:text-surface-600'" class="flex-1 py-1.5 text-[10px] font-bold rounded-lg transition-all">ALL</button>
                            <button @click="activeCategory = 'hero'" :class="activeCategory === 'hero' ? 'bg-white dark:bg-surface-700 text-primary-600 shadow-sm' : 'text-surface-400 hover:text-surface-600'" class="flex-1 py-1.5 text-[10px] font-bold rounded-lg transition-all">HERO</button>
                            <button @click="activeCategory = 'content'" :class="activeCategory === 'content' ? 'bg-white dark:bg-surface-700 text-primary-600 shadow-sm' : 'text-surface-400 hover:text-surface-600'" class="flex-1 py-1.5 text-[10px] font-bold rounded-lg transition-all">UI</button>
                            <button @click="activeCategory = 'logic'" :class="activeCategory === 'logic' ? 'bg-white dark:bg-surface-700 text-primary-600 shadow-sm' : 'text-surface-400 hover:text-surface-600'" class="flex-1 py-1.5 text-[10px] font-bold rounded-lg transition-all">LOGIC</button>
                        </div>

                        <div class="max-h-[60vh] overflow-y-auto p-4 space-y-3 custom-scrollbar">
                            @foreach($sectionTypes as $type => $info)
                            <form method="POST" action="{{ route('admin.builder.sections.add', $page) }}" 
                                  x-show="activeCategory === 'all' || '{{ $info['category'] ?? 'content' }}' === activeCategory">
                                @csrf
                                <input type="hidden" name="type" value="{{ $type }}">
                                <button type="submit" class="w-full flex items-center gap-4 p-3 rounded-2xl text-left bg-surface-50/50 dark:bg-surface-800/50 border border-transparent hover:border-primary-500/30 hover:bg-white dark:hover:bg-surface-700 transition-all group overflow-hidden relative">
                                    <div class="absolute inset-0 bg-gradient-to-br from-primary-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <div class="w-10 h-10 rounded-xl bg-white dark:bg-surface-800 shadow-sm flex items-center justify-center flex-shrink-0 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300 relative z-10">
                                        @if(str_contains($type, 'hero'))
                                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"/></svg>
                                        @elseif(str_contains($type, 'product'))
                                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                        @else
                                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        @endif
                                    </div>
                                    <div class="relative z-10">
                                        <p class="text-[13px] font-bold text-surface-800 dark:text-white">{{ $info['label'] }}</p>
                                        <p class="text-[10px] text-surface-400 leading-tight line-clamp-1">{{ $info['description'] }}</p>
                                    </div>
                                </button>
                            </form>
                            @endforeach
                        </div>
                    </div>

                    {{-- Page Statistics Card --}}
                    <div class="p-5 bg-gradient-to-br from-surface-800 to-black rounded-[2rem] text-white shadow-xl">
                        <h4 class="text-[10px] font-black text-surface-400 uppercase tracking-widest mb-3">Architect Status</h4>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-surface-400">Total Weight</span>
                                <span class="text-xs font-mono">{{ count($page->sections) }} Units</span>
                            </div>
                            <div class="flex items-center justify-between font-bold">
                                <span class="text-xs text-surface-400">Sync Status</span>
                                <span class="text-xs text-emerald-400 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                                    Linked
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Main Canvas (Center) --}}
            <main class="lg:col-span-9 flex flex-col items-center">
                <div class="space-y-6 w-full" 
                     id="architecture-canvas"
                     :class="{
                        'max-w-full': activeDevice === 'desktop',
                        'max-w-[768px]': activeDevice === 'tablet',
                        'max-w-[425px]': activeDevice === 'mobile'
                     }">
                    @forelse($page->sections as $section)
                    <div class="builder-section group/section relative bg-white dark:bg-surface-800/40 backdrop-blur-md rounded-[2.5rem] border border-surface-200 dark:border-surface-700/50 overflow-hidden shadow-sm hover:shadow-2xl hover:shadow-primary-500/5" 
                         x-data="{ editing: false, activeTab: 'content' }"
                         data-id="{{ $section->id }}">
                        
                        {{-- Section Header Overlay --}}
                        <div class="absolute top-4 right-6 z-20 flex items-center gap-2 transition-opacity">
                            <button @click="editing = !editing" 
                                    class="w-10 h-10 flex items-center justify-center rounded-2xl bg-white/90 dark:bg-surface-800/90 shadow-lg border border-surface-200 dark:border-surface-700 text-surface-600 dark:text-surface-300 hover:text-primary-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <form method="POST" action="{{ route('admin.builder.sections.toggle', $section) }}">
                                @csrf
                                <button type="submit" class="w-10 h-10 flex items-center justify-center rounded-2xl bg-white/90 dark:bg-surface-800/90 shadow-lg border border-surface-200 dark:border-surface-700 text-surface-600 dark:text-surface-300 hover:text-amber-500 transition-colors" title="{{ $section->is_active ? 'Hide' : 'Show' }}">
                                    @if($section->is_active)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    @endif
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.builder.sections.delete', $section) }}" onsubmit="return confirm('Archive this section?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-10 h-10 flex items-center justify-center rounded-2xl bg-white/90 dark:bg-surface-800/90 shadow-lg border border-surface-200 dark:border-surface-700 text-red-500 hover:bg-red-500 hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>

                        {{-- Section Meta --}}
                        <div class="px-8 py-4 bg-surface-50 dark:bg-surface-700/30 flex items-center justify-between border-b border-surface-100 dark:border-surface-700/50">
                            <div class="flex items-center gap-4">
                                <div class="cursor-grab active:cursor-grabbing select-none p-1.5 hover:bg-white dark:hover:bg-surface-700 rounded-lg text-surface-400 hover:text-primary-600 transition-all">
                                    <svg class="w-5 h-5 handle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $section->is_active ? 'bg-emerald-500' : 'bg-surface-300 dark:bg-surface-600' }}"></span>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-surface-500">{{ str_replace('_', ' ', $section->type) }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Section Editor (Visible on Edit) --}}
                        <div x-show="editing" class="border-t border-surface-100 dark:border-surface-700/50 bg-white dark:bg-surface-900/50">
                            <div class="p-8">
                                <div class="mb-6 flex items-center justify-between">
                                    <h3 class="text-sm font-black uppercase tracking-widest text-primary-500">Configure: {{ str_replace('_', ' ', $section->type) }}</h3>
                                    <span class="text-[10px] text-surface-400 font-mono">ID: #{{ $section->id }}</span>
                                </div>
                                <form method="POST" action="{{ route('admin.builder.sections.update', $section) }}">
                                    @csrf @method('PUT')
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        @foreach($section->contents as $content)
                                        <div class="{{ in_array($content->key, ['content', 'subtitle', 'description']) ? 'md:col-span-2' : '' }} group/field">
                                            <label class="block text-[10px] font-black text-surface-400 uppercase tracking-widest mb-2 group-focus-within/field:text-primary-500 transition-colors">
                                                {{ str_replace('_', ' ', $content->key) }}
                                            </label>
                                            @if(in_array($content->key, ['content', 'description', 'review_1', 'review_2', 'html', 'css', 'js']))
                                                <textarea name="contents[{{ $content->id }}]" rows="{{ in_array($content->key, ['html', 'css', 'js']) ? 8 : 3 }}" class="w-full px-5 py-3.5 bg-surface-50 dark:bg-surface-800 border-2 border-transparent focus:border-primary-500/20 focus:bg-white dark:focus:bg-surface-700 rounded-2xl text-sm font-mono transition-all dark:text-white outline-none">{{ $content->value }}</textarea>
                                            @else
                                                <input type="text" name="contents[{{ $content->id }}]" value="{{ $content->value }}" class="w-full px-5 py-3.5 bg-surface-50 dark:bg-surface-800 border-2 border-transparent focus:border-primary-500/20 focus:bg-white dark:focus:bg-surface-700 rounded-2xl text-sm transition-all dark:text-white outline-none">
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    <div class="flex items-center gap-4 mt-8 pt-6 border-t border-surface-50 dark:border-surface-700/50">
                                        <button type="submit" class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white text-sm font-black rounded-2xl transition-all shadow-lg shadow-primary-500/20">Commit Changes</button>
                                        <button type="button" @click="editing = false" class="px-8 py-3 bg-surface-100 dark:bg-surface-700 hover:bg-surface-200 dark:hover:bg-surface-600 text-[13px] font-bold rounded-2xl transition-all dark:text-white">Discard</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Dynamic Preview (Visible on View) --}}
                        <div x-show="!editing" class="p-8 {{ !$section->is_active ? 'opacity-40 grayscale' : '' }} transition-all duration-700">
                            @include('admin.builder.partials.preview-render', ['section' => $section])
                        </div>
                    </div>
                    @empty
                    <div class="py-32 flex flex-col items-center justify-center text-center opacity-50">
                        <div class="w-24 h-24 bg-surface-100 dark:bg-surface-800 rounded-3xl mb-6 flex items-center justify-center border-2 border-dashed border-surface-300 dark:border-surface-600">
                            <svg class="w-12 h-12 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <h4 class="text-xl font-display font-bold text-surface-800 dark:text-white">Canvas Empty</h4>
                        <p class="text-sm text-surface-500 max-w-xs mt-2">Add your first infrastructure block from the sidebar library.</p>
                    </div>
                    @endforelse
                </div>
            </main>
        </div>

        {{-- Global Command Bar (Bottom Floating) --}}
        <div class="fixed bottom-8 left-1/2 -translate-x-1/2 z-50 w-full max-w-xl px-4 animate-slide-up" style="animation-delay: 0.5s; animation-fill-mode: both;">
            <div class="bg-surface-900/90 dark:bg-surface-800/90 backdrop-blur-2xl px-6 py-4 rounded-[2rem] border border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.5)] flex items-center justify-between gap-6 text-white">
                <div class="flex items-center gap-4">
                    <div class="p-2.5 bg-primary-500 rounded-xl">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                    </div>
                    <div>
                        <p class="text-[13px] font-black uppercase tracking-tighter">{{ $page->page_name }}</p>
                        <p class="text-[10px] text-surface-400 leading-none">Draft synchronized on-cloud</p>
                    </div>
                </div>
                <div class="hidden md:flex items-center gap-1 bg-white/5 p-1 rounded-2xl border border-white/10">
                    <button @click="activeDevice = 'desktop'" :class="activeDevice === 'desktop' ? 'bg-white text-primary-600' : 'text-surface-400 hover:text-white'" class="p-2 rounded-xl transition-all" title="Desktop View">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </button>
                    <button @click="activeDevice = 'tablet'" :class="activeDevice === 'tablet' ? 'bg-white text-primary-600' : 'text-surface-400 hover:text-white'" class="p-2 rounded-xl transition-all" title="Tablet View">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </button>
                    <button @click="activeDevice = 'mobile'" :class="activeDevice === 'mobile' ? 'bg-white text-primary-600' : 'text-surface-400 hover:text-white'" class="p-2 rounded-xl transition-all" title="Mobile View">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.builder.preview', $page) }}" target="_blank" class="px-5 py-2.5 bg-white/10 hover:bg-white/20 rounded-xl text-xs font-bold transition-all border border-white/5">Preview</a>
                    <button onclick="window.location.reload()" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-500 rounded-xl text-xs font-black tracking-widest transition-all shadow-lg shadow-primary-500/20 active:scale-95">SYNC ALL</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const el = document.getElementById('architecture-canvas');
            if (el) {
                console.log('Sortable initializing on:', el);
                new Sortable(el, {
                    animation: 300,
                    handle: '.cursor-grab',
                    ghostClass: 'architect-ghost',
                    dragClass: 'architect-drag',
                    chosenClass: 'architect-chosen',
                    fallbackTolerance: 3,
                    onStart: function(evt) {
                        console.log('Drag started', evt);
                        document.body.classList.add('dragging-active');
                    },
                    onEnd: function(evt) {
                        console.log('Drag ended', evt);
                        document.body.classList.remove('dragging-active');
                        
                        // Only save if order actually changed
                        if (evt.oldIndex !== evt.newIndex) {
                            const order = Array.from(el.querySelectorAll('.builder-section')).map(section => section.dataset.id);
                            console.log('New order detected, saving...', order);

                            fetch("{{ route('admin.builder.reorder', $page) }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                },
                                body: JSON.stringify({ order: order })
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log('Save response:', data);
                                if (data.success) {
                                    // Success!
                                }
                            })
                            .catch(err => console.error('Save failed:', err));
                        }
                    }
                });
            }
        });
    </script>
    @endpush

    <style>
        #architecture-canvas {
            /* Remove transitions during drag-and-drop to prevent positioning glitches */
            transition: none !important;
        }
        .architect-ghost {
            opacity: 0.2 !important;
            background: #6366f1 !important;
            border: 2px dashed #4f46e5 !important;
            border-radius: 2.5rem !important;
        }
        .architect-drag {
            opacity: 1 !important;
            transform: scale(1.02) rotate(1deg) !important;
            box-shadow: 0 40px 80px -12px rgba(99, 102, 241, 0.35) !important;
            z-index: 1000 !important;
        }
        .architect-chosen {
            border: 2px solid #6366f1 !important;
        }
        .dragging-active {
            cursor: grabbing !important;
            user-select: none !important;
        }
        .animate-slide-up {
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes slideUp {
            from { transform: translate(-50%, 100px); opacity: 0; }
            to { transform: translate(-50%, 0); opacity: 1; }
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
    </style>
</x-layouts.admin>
