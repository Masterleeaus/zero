@extends('panel.layout.settings')
@section('title', __('Business Accounts'))
@section('titlebar_actions', '')

@section('settings')
    <form
        class="flex flex-col gap-5"
        method="POST"
        action="{{ route('dashboard.admin.frontend.business-accounts.save') }}"
    >
        <div class="row mb-4">
            <div class="col-md-12">
                <div
                    class="flex flex-col space-y-1"
                    id="menu-items"
                >

                    <div class="menu-item relative rounded-lg border !bg-white shadow-[0_10px_10px_rgba(0,0,0,0.06)] dark:!bg-opacity-5">
                        @foreach ($accounts as $account)
                            <h4 class="accordion-title mb-0 flex cursor-pointer items-center justify-between !gap-1 !py-1 !pe-2 !ps-4">
                                <span>{{ $account->title }}</span>
                                <div class="accordion-controls flex items-center">
                                    <span class="handle size-10 inline-flex cursor-move items-center justify-center rounded-md hover:bg-black hover:!bg-opacity-10 dark:hover:bg-white">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                            fill="none"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path d="M9 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                            <path d="M9 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                            <path d="M9 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                            <path d="M15 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                            <path d="M15 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                            <path d="M15 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                        </svg>
                                    </span>
                                </div>
                            </h4>
                            <div class="accordion-content mt-3 hidden p-3 pt-0">
                                <x-forms.input
                                    class="my-3"
                                    id="subtitle"
                                    label="{{ __('Subtitle') }}"
                                    name="subtitle_{{ $account->key }}"
                                    size="lg"
                                    required
                                    value="{{ $account->subtitle }}"
                                />
                                <x-forms.input
                                    class="my-3"
                                    id="link"
                                    label="{{ __('Link') }}"
                                    name="link_{{ $account->key }}"
                                    size="lg"
                                    required
                                    value="{{ $account->link }}"
                                />
                                <x-forms.input
                                    class="my-3"
                                    id="icon"
                                    label="{{ __('Icon (SVG)') }}"
                                    name="icon_{{ $account->key }}"
                                    size="lg"
                                    required
                                    value="{!! $account->icon !!}"
                                />
                                <x-forms.input
                                    id="is_active"
                                    size="lg"
                                    name="is_active_{{ $account->key }}"
                                    type="checkbox"
                                    label="{{ __('Is Active') }}"
                                    :checked="$account->is_active == true"
                                    switcher
                                />
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <button
            class="btn btn-primary w-full"
            type="submit"
        >
            {{ __('Save') }}
        </button>
    </form>
@endsection

@push('script')
    <script>
        $('body').on('click', '.accordion-title', ev => {
            const accordionTitle = ev.currentTarget;
            accordionTitle.classList.toggle("active");
            accordionTitle.nextElementSibling.classList.toggle("hidden");
        });
    </script>
@endpush
