<div class="custom-code-section py-12 px-6">
    <style>
        {!! $section->getContent('css') !!}
    </style>
    
    <div class="custom-html-wrapper">
        {!! $section->getContent('html') !!}
    </div>

    <script>
        (function() {
            {!! $section->getContent('js') !!}
        })();
    </script>
</div>
