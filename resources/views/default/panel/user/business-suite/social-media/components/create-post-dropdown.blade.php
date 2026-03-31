<x-dropdown.dropdown
    anchor="end"
    offsetY="13px"
>
    <x-slot:trigger
        variant="primary"
    >
        <x-tabler-plus class="size-4" />
        @lang('Generate New Work Draft')
    </x-slot:trigger>

    <x-slot:dropdown
        class="min-w-52 overflow-hidden p-2"
    >
        @php
            $draftTypes = [
                ['label' => __('New Booking'), 'type' => 'booking'],
                ['label' => __('New Quote'), 'type' => 'quote'],
                ['label' => __('New Service Job'), 'type' => 'service_job'],
                ['label' => __('New Invoice'), 'type' => 'invoice'],
                ['label' => __('New Report'), 'type' => 'report'],
            ];
        @endphp
        @foreach ($draftTypes as $draft)
            <x-button
                class="w-full justify-start rounded-md px-3 py-2 text-start text-2xs hover:bg-heading-foreground/5 hover:no-underline"
                variant="link"
                href="{{ route('dashboard.user.social-media.post.create', ['draft_type' => $draft['type']]) }}"
            >
                {{ $draft['label'] }}
            </x-button>
        @endforeach
    </x-slot:dropdown>
</x-dropdown.dropdown>
