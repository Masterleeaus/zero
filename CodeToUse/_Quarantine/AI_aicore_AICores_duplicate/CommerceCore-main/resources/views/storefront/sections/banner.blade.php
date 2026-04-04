<section class="bg-gradient-to-r from-amber-500 to-orange-600 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h2 class="text-3xl sm:text-4xl font-display font-bold">{{ $section->getContent('title', 'Special Offer') }}</h2>
        <p class="mt-3 text-amber-100 text-lg max-w-xl mx-auto">{{ $section->getContent('subtitle') }}</p>
        @if($section->getContent('button_text'))
        <div class="mt-8">
            <a href="{{ $section->getContent('button_url', '#') }}" class="inline-flex items-center gap-2 px-8 py-3 bg-white text-amber-700 font-bold rounded-xl hover:-translate-y-0.5 transition-all shadow-lg">{{ $section->getContent('button_text') }}</a>
        </div>
        @endif
    </div>
</section>
