@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ __('managedpremises::pm.properties') }}</h4>
        @if(function_exists('user_can') ? user_can('managedpremises.create') : true)
<x-forms.button-primary :link="route('managedpremises.properties.create')" icon="plus">
                @lang('app.add')
            </x-forms.button-primary>
        @endif
    </div>
    <div class="row mb-3">
        <div class="col-lg-6 mb-3">
            @include('managedpremises::widgets.next_visits')
        </div>
        <div class="col-lg-6 mb-3">
            @include('managedpremises::widgets.overdue_inspections')
        </div>
    </div>


    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>@lang('managedpremises::app.labels.name')</th>
                            <th>@lang('managedpremises::app.labels.type')</th>
                            <th>@lang('managedpremises::app.labels.status')</th>
                            <th>@lang('managedpremises::app.labels.address')</th>
                            <th class="text-right">@lang('app.action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($properties as $property)
                            <tr>
                                <td>
                                    <a href="{{ route('managedpremises.properties.show', $property->id) }}">
                                        {{ $property->name ?? ('#' . $property->id) }}
                                    </a>
                                    @if ($property->property_code)
                                        <div class="text-muted f-12">{{ $property->property_code }}</div>
                                    @endif
                                </td>
                                <td>{{ ucfirst($property->type) }}</td>
                                <td>{{ ucfirst($property->status) }}</td>
                                <td>{{ trim(implode(' ', array_filter([$property->address_line1, $property->suburb, $property->state, $property->postcode]))) }}</td>
                                <td class="text-right">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('managedpremises.properties.edit', $property->id) }}">@lang('app.edit')</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">@lang('app.noRecordFound')</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $properties->links() }}
            </div>
        </div>
    </div>
@endsection
