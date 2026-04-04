<section class="py-24 bg-primary-600 relative overflow-hidden">
    {{-- Decorative Background Elements --}}
    <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/2 w-96 h-96 bg-primary-900/20 rounded-full blur-3xl"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-[3rem] p-8 md:p-16 text-center">
            <div class="max-w-2xl mx-auto">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-2xl mb-8 shadow-xl">
                    <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L22 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                
                <h2 class="text-3xl md:text-5xl font-display font-extrabold text-white mb-4 tracking-tight">
                    {{ $section->getContent('title') }}
                </h2>
                <p class="text-primary-100 text-lg md:text-xl mb-10">
                    {{ $section->getContent('subtitle') }}
                </p>

                <form x-data="newsletterForm('{{ $store->slug }}')" @submit.prevent="submit" class="relative max-w-lg mx-auto">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input 
                            type="email" 
                            x-model="email" 
                            placeholder="{{ $section->getContent('placeholder') }}" 
                            required
                            class="flex-1 px-6 py-4 bg-white/10 border border-white/30 rounded-2xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all font-medium"
                        >
                        <button 
                            type="submit" 
                            :disabled="loading"
                            class="px-8 py-4 bg-white text-primary-700 font-display font-bold rounded-2xl shadow-xl hover:shadow-2xl hover:-translate-y-0.5 active:translate-y-0 transition-all disabled:opacity-50"
                        >
                            <span x-show="!loading">{{ $section->getContent('button_text') }}</span>
                            <span x-show="loading" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-primary-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Sending...
                            </span>
                        </button>
                    </div>

                    <p x-show="message" x-transition class="mt-4 text-sm font-bold text-white bg-white/20 inline-block px-4 py-2 rounded-lg" x-text="message"></p>
                </form>
            </div>
        </div>
    </div>
</section>

@once
@push('scripts')
<script>
function newsletterForm(storeSlug) {
    return {
        email: '',
        loading: false,
        message: '',
        async submit() {
            this.loading = true;
            this.message = '';
            
            try {
                const response = await fetch(`/s/${storeSlug}/newsletter/subscribe`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ email: this.email })
                });
                
                const data = await response.json();
                this.message = data.message;
                if (data.success) {
                    this.email = '';
                    setTimeout(() => this.message = '', 5000);
                }
            } catch (error) {
                this.message = 'Something went wrong. Please try again.';
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush
@endonce
