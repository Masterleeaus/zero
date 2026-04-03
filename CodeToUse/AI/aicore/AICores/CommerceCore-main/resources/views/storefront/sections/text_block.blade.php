<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="max-w-3xl mx-auto">
        <h2 class="text-3xl font-display font-bold text-surface-800 dark:text-white text-center mb-6">{{ $section->getContent('title', 'About Us') }}</h2>
        <div class="prose prose-lg dark:prose-invert mx-auto text-surface-600 dark:text-surface-300 text-center leading-relaxed">
            {{ $section->getContent('content') }}
        </div>
    </div>
</section>
