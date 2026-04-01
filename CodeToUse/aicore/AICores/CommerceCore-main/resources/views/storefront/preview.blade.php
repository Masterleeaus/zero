<x-layouts.storefront :store="$store">
    @foreach($page->activeSections as $section)
        @include('storefront.sections.' . $section->type, ['section' => $section, 'store' => $store])
    @endforeach
</x-layouts.storefront>
