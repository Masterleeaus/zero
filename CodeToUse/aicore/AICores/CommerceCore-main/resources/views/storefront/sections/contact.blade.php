<section class="py-24 bg-surface-50 dark:bg-surface-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start">
            
            {{-- Contact Info side --}}
            <div>
                <h2 class="text-3xl md:text-5xl font-display font-extrabold text-surface-900 dark:text-white mb-6">
                    {{ $section->getContent('title') }}
                </h2>
                <p class="text-surface-600 dark:text-surface-400 text-lg mb-12">
                    {{ $section->getContent('subtitle') }}
                </p>

                <div class="space-y-8">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-surface-900 dark:text-white">Our Location</h4>
                            <p class="text-surface-600 dark:text-surface-400">{{ $store->address ?? 'Global Operations' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L22 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-surface-900 dark:text-white">Email Us</h4>
                            <p class="text-surface-600 dark:text-surface-400">{{ $store->email ?? 'support@example.com' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Side --}}
            <div class="bg-white dark:bg-surface-800 rounded-[2.5rem] p-8 md:p-12 shadow-soft-xl border border-surface-100 dark:border-surface-700">
                <form x-data="contactForm('{{ $store->slug }}')" @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-surface-700 dark:text-surface-300 mb-2">{{ $section->getContent('name_label') }}</label>
                            <input type="text" x-model="form.name" required class="w-full px-5 py-4 bg-surface-50 dark:bg-surface-900 border border-surface-200 dark:border-surface-700 rounded-2xl focus:ring-2 focus:ring-primary-500 outline-none transition-all dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-surface-700 dark:text-surface-300 mb-2">{{ $section->getContent('email_label') }}</label>
                            <input type="email" x-model="form.email" required class="w-full px-5 py-4 bg-surface-50 dark:bg-surface-900 border border-surface-200 dark:border-surface-700 rounded-2xl focus:ring-2 focus:ring-primary-500 outline-none transition-all dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-surface-700 dark:text-surface-300 mb-2">{{ $section->getContent('subject_label') }}</label>
                        <input type="text" x-model="form.subject" class="w-full px-5 py-4 bg-surface-50 dark:bg-surface-900 border border-surface-200 dark:border-surface-700 rounded-2xl focus:ring-2 focus:ring-primary-500 outline-none transition-all dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-surface-700 dark:text-surface-300 mb-2">{{ $section->getContent('message_label') }}</label>
                        <textarea x-model="form.message" rows="4" required class="w-full px-5 py-4 bg-surface-50 dark:bg-surface-900 border border-surface-200 dark:border-surface-700 rounded-2xl focus:ring-2 focus:ring-primary-500 outline-none transition-all dark:text-white"></textarea>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="loading"
                        class="w-full py-5 bg-primary-600 text-white font-display font-bold rounded-2xl shadow-lg hover:bg-primary-700 hover:-translate-y-1 transition-all disabled:opacity-50"
                    >
                        <span x-show="!loading">{{ $section->getContent('button_text') }}</span>
                        <span x-show="loading">Sending...</span>
                    </button>

                    <p x-show="message" x-transition class="mt-4 text-sm font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 px-4 py-2 rounded-xl text-center" x-text="message"></p>
                </form>
            </div>
        </div>
    </div>
</section>

@once
@push('scripts')
<script>
function contactForm(storeSlug) {
    return {
        form: { name: '', email: '', subject: '', message: '' },
        loading: false,
        message: '',
        async submit() {
            this.loading = true;
            this.message = '';
            
            try {
                const response = await fetch(`/s/${storeSlug}/contact/submit`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.form)
                });
                
                const data = await response.json();
                this.message = data.message;
                if (data.success) {
                    this.form = { name: '', email: '', subject: '', message: '' };
                    setTimeout(() => this.message = '', 10000);
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
