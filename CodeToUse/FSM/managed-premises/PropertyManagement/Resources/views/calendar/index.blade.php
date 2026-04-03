@extends('layouts.app')

@section('title', __('propertymanagement::pm.calendar'))

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="mb-0">{{ __('propertymanagement::pm.calendar') }}</h3>
        <form method="get" class="d-flex gap-2">
            <input type="month" name="month" value="{{ $month }}" class="form-control" style="max-width: 220px;">
            <button class="btn btn-primary">{{ __('propertymanagement::pm.view') }}</button>
        </form>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <p class="mb-0 text-muted">
                {{ __('propertymanagement::pm.showing') }} {{ $start->format('d M Y') }} → {{ $end->format('d M Y') }}
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">{{ __('propertymanagement::pm.visits') }}</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('propertymanagement::pm.when') }}</th>
                                <th>{{ __('propertymanagement::pm.property') }}</th>
                                <th>{{ __('propertymanagement::pm.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($visits as $v)
                            <tr>
                                <td>{{ optional($v->scheduled_for)->format('d M Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('propertymanagement.properties.show', $v->property_id) }}">
                                        {{ $v->property?->name ?? ('#'.$v->property_id) }}
                                    </a>
                                </td>
                                <td><span class="badge bg-secondary">{{ $v->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted p-4">{{ __('propertymanagement::pm.none') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">{{ __('propertymanagement::pm.inspections') }}</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('propertymanagement::pm.when') }}</th>
                                <th>{{ __('propertymanagement::pm.property') }}</th>
                                <th>{{ __('propertymanagement::pm.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($inspections as $i)
                            <tr>
                                <td>{{ optional($i->scheduled_for)->format('d M Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('propertymanagement.properties.show', $i->property_id) }}">
                                        {{ $i->property?->name ?? ('#'.$i->property_id) }}
                                    </a>
                                </td>
                                <td><span class="badge bg-secondary">{{ $i->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted p-4">{{ __('propertymanagement::pm.none') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
