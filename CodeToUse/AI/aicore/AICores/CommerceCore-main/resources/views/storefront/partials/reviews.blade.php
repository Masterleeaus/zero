<div class="mt-16 border-t border-surface-100 dark:border-surface-800 pt-16">
    <div class="flex justify-between items-center mb-12">
        <h3 class="text-3xl font-black text-surface-900 dark:text-white uppercase tracking-tighter">Customer Reviews</h3>
        <div class="flex items-center gap-4 bg-surface-50 dark:bg-surface-800 px-6 py-3 rounded-2xl border border-surface-100 dark:border-surface-700 shadow-sm">
            <span class="text-2xl font-black text-surface-900 dark:text-white">{{ number_format($product->reviews()->avg('rating'), 1) }}</span>
            <div class="flex text-amber-500">
                @for($i=0; $i<5; $i++)
                    <svg class="w-4 h-4 {{ $i < floor($product->reviews()->avg('rating')) ? 'fill-current' : '' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.382-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                @endfor
            </div>
            <span class="text-[10px] text-surface-400 font-bold uppercase tracking-widest">{{ $product->reviews()->count() }} Total</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <!-- Review Form -->
        <div class="lg:col-span-1">
            <div class="sticky top-8 bg-white dark:bg-surface-800 p-8 rounded-3xl border border-surface-200 dark:border-surface-700 shadow-xl shadow-indigo-500/5">
                <h4 class="text-lg font-black text-surface-900 dark:text-white mb-2 uppercase">Share Your Experience</h4>
                <p class="text-[10px] text-surface-500 mb-6 font-bold uppercase tracking-widest">Only verified buyers can review products.</p>
                
                @auth
                <form action="{{ route('storefront.product.review', ['store' => $storeSlug, 'product' => $product->id]) }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-[10px] text-surface-400 font-black uppercase tracking-widest mb-3 italic">Overall Rating</label>
                        <div class="flex gap-4">
                            @foreach([1, 2, 3, 4, 5] as $star)
                            <label class="cursor-pointer group">
                                <input type="radio" name="rating" value="{{ $star }}" class="hidden peer" {{ $star == 5 ? 'checked' : '' }}>
                                <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-surface-50 dark:bg-surface-900 border-2 border-transparent peer-checked:border-indigo-600 peer-checked:bg-white dark:peer-checked:bg-indigo-600/20 group-hover:scale-110 transition-all">
                                    <span class="text-sm font-black text-surface-400 peer-checked:text-indigo-600 dark:peer-checked:text-indigo-400 group-hover:text-surface-900">{{ $star }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] text-surface-400 font-black uppercase tracking-widest mb-2 italic">Product Feedback</label>
                        <textarea name="comment" rows="4" placeholder="How was it? Design, speed, quality..." 
                            class="w-full text-xs font-bold border-surface-100 rounded-2xl dark:bg-surface-900 dark:text-white dark:border-surface-700 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 placeholder-surface-300"></textarea>
                    </div>

                    <button type="submit" class="w-full py-4 bg-indigo-600 text-white text-xs font-black rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-500/30 uppercase tracking-widest active:scale-95">SUBMIT REVIEW</button>
                </form>
                @else
                <div class="p-6 bg-surface-50 dark:bg-surface-900 rounded-2xl text-center border-2 border-dashed border-surface-200">
                    <p class="text-[10px] font-black text-surface-400 uppercase tracking-widest leading-relaxed">Please <a href="/login" class="text-indigo-600 hover:underline">sign in</a> to review products.</p>
                </div>
                @endauth
            </div>
        </div>

        <!-- Review List -->
        <div class="lg:col-span-2 space-y-8">
            @forelse($product->reviews()->latest()->get() as $review)
            <div class="p-8 bg-white dark:bg-surface-800 rounded-3xl border border-surface-100 dark:border-surface-700 shadow-sm relative group overflow-hidden">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center">
                            <span class="text-xs font-black text-indigo-600">{{ substr($review->user->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <h5 class="text-sm font-black text-surface-900 dark:text-white uppercase">{{ $review->user->name }}</h5>
                            <p class="text-[8px] text-surface-400 font-bold uppercase tracking-widest">{{ $review->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <div class="flex text-amber-500">
                        @for($i=0; $i<$review->rating; $i++)
                            <svg class="w-3 h-3 fill-current" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.382-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                        @endfor
                    </div>
                </div>
                <p class="text-xs text-surface-600 dark:text-surface-400 leading-relaxed font-bold italic">{{ $review->comment ?? 'Excellent product!' }}</p>
            </div>
            @empty
            <div class="py-20 text-center border-2 border-dashed border-surface-100 dark:border-surface-800 rounded-4xl">
                <svg class="w-12 h-12 text-surface-100 dark:text-surface-800 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <p class="text-xs font-black text-surface-200 uppercase tracking-widest">No reviews yet. Be the first!</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
