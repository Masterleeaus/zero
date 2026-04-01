<section class="py-24 bg-surface-50 dark:bg-surface-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl lg:text-4xl font-display font-bold text-surface-900 dark:text-white tracking-tight">
                {{ $section->getContent('title') }}
            </h2>
        </div>

        <div class="space-y-4" x-data="{ active: null }">
            {{-- FAQ 1 --}}
            <div class="bg-white dark:bg-surface-800 rounded-2xl border border-surface-200 dark:border-surface-700 overflow-hidden text-left shadow-sm">
                <button @click="active = active === 1 ? null : 1" class="w-full px-6 py-5 flex items-center justify-between text-left focus:outline-none focus:bg-surface-50 hover:bg-surface-50 dark:hover:bg-surface-700/50 transition-colors">
                    <span class="font-bold text-lg text-surface-900 dark:text-white">{{ $section->getContent('q1') }}</span>
                    <span class="w-8 h-8 rounded-full bg-surface-100 dark:bg-surface-700 flex items-center justify-center flex-shrink-0 transition-colors" :class="active === 1 ? 'text-primary-600 bg-primary-50 dark:bg-primary-900/40 dark:text-primary-400' : 'text-surface-500'">
                        <svg class="w-5 h-5 transform transition-transform duration-200" :class="active === 1 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </button>
                <div x-show="active === 1" x-collapse>
                    <div class="px-6 pb-6 text-surface-600 dark:text-surface-300">
                        {{ $section->getContent('a1') }}
                    </div>
                </div>
            </div>

            {{-- FAQ 2 --}}
            <div class="bg-white dark:bg-surface-800 rounded-2xl border border-surface-200 dark:border-surface-700 overflow-hidden text-left shadow-sm">
                <button @click="active = active === 2 ? null : 2" class="w-full px-6 py-5 flex items-center justify-between text-left focus:outline-none focus:bg-surface-50 hover:bg-surface-50 dark:hover:bg-surface-700/50 transition-colors">
                    <span class="font-bold text-lg text-surface-900 dark:text-white">{{ $section->getContent('q2') }}</span>
                    <span class="w-8 h-8 rounded-full bg-surface-100 dark:bg-surface-700 flex items-center justify-center flex-shrink-0 transition-colors" :class="active === 2 ? 'text-primary-600 bg-primary-50 dark:bg-primary-900/40 dark:text-primary-400' : 'text-surface-500'">
                        <svg class="w-5 h-5 transform transition-transform duration-200" :class="active === 2 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </button>
                <div x-show="active === 2" x-collapse>
                    <div class="px-6 pb-6 text-surface-600 dark:text-surface-300">
                        {{ $section->getContent('a2') }}
                    </div>
                </div>
            </div>
            
            {{-- Contact Fallback --}}
            <div class="pt-8 mt-8 border-t border-surface-200 dark:border-surface-700 text-center">
                <p class="text-surface-500">Still have questions?</p>
                <a class="text-primary-600 dark:text-primary-400 font-bold hover:underline" href="{{ route('storefront.home', $store->slug ?? 'demo') }}">Contact our Support Team &rarr;</a>
            </div>
        </div>
    </div>
</section>
