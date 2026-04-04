@extends('panel.layout.app')
@section('title', __('Support Requests'))
@section('titlebar_actions')
    @if (!Auth::user()->isAdmin())
        <x-button href="{{ route('dashboard.support.new') }}">
            {{ __('Create New Support Request') }}
            <x-tabler-plus class="size-4" />
        </x-button>
    @endif
@endsection
@section('content')
    <div class="py-10 space-y-4">
        <div class="flex items-center justify-between">
            <form method="get" class="flex items-center gap-3">
                <x-form.select name="status" onchange="this.form.submit()">
                    <option value="">{{ __('All statuses') }}</option>
                    <option value="open" @selected(($status ?? null) === 'open')>{{ __('Open') }}</option>
                    <option value="waiting_on_user" @selected(($status ?? null) === 'waiting_on_user')>{{ __('Waiting on user') }}</option>
                    <option value="waiting_on_team" @selected(($status ?? null) === 'waiting_on_team')>{{ __('Waiting on team') }}</option>
                    <option value="resolved" @selected(($status ?? null) === 'resolved')>{{ __('Resolved') }}</option>
                </x-form.select>
            </form>
        </div>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>
                        {{ __('Ticked ID') }}
                    </th>
                    <th>
                        {{ __('Status') }}
                    </th>
                    <th>
                        {{ __('Category') }}
                    </th>
                    <th>
                        {{ __('Subject') }}
                    </th>
                    <th>
                        {{ __('Priority') }}
                    </th>
                    <th>
                        {{ __('Created At') }}
                    </th>
                    <th>
                        {{ __('Last Updated') }}
                    </th>
                    <th class="text-end">
                        {{ __('Actions') }}
                    </th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @foreach ($items as $entry)
                    <tr>
                        <td>
                            {{ $entry->ticket_id }}
                        </td>
                        <td>
                            <x-badge
                                class="whitespace-nowrap text-2xs"
                                variant="{{ in_array($entry->status, ['resolved']) ? 'success' : 'secondary' }}"
                            >
                                {{ __($entry->status) }}
                            </x-badge>
                        </td>
                        <td>
                            {{ __($entry->category) }}
                        </td>
                        <td>
                            {{ __($entry->subject) }}
                        </td>
                        <td>
                            {{ __($entry->priority) }}
                        </td>
                        <td>
                            {{ $entry->created_at }}
                        </td>
                        <td>
                            {{ $entry->updated_at }}
                        </td>
                        <td class="whitespace-nowrap text-end">
                            <x-button
                                size="sm"
                                href="{{ route('dashboard.support.view', $entry->ticket_id) }}"
                            >
                                {{ __('View') }}
                            </x-button>
                        </td>
                    </tr>
                @endforeach

            </x-slot:body>
        </x-table>
        <div>
            {{ $items->withQueryString()->links() }}
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ custom_theme_url('/assets/libs/tom-select/dist/js/tom-select.base.min.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/js/panel/support.js') }}"></script>
@endpush
