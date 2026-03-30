@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="mb-1">{{ $property->name ?? ('Property #' . $property->id) }}</h4>
            <div class="text-muted">{{ trim(implode(' ', array_filter([$property->address_line1, $property->suburb, $property->state, $property->postcode]))) }}</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('managedpremises.properties.edit', $property->id) }}">@lang('app.edit')</a>
        </div>
    </div>

    {{-- Titan Zero integration (safe include) --}}
    @if (config('managedpremises.integrations.titan_zero') && user()->permission('titanzero.use') !== 'none')
        @includeIf('titanzero::partials.ask-titan', [
            'context' => [
                'module' => 'managedpremises',
                'page' => 'property_show',
                'record' => [
                    'type' => 'property',
                    'id' => $property->id,
                    'name' => $property->name,
                    'address' => $property->address_line1,
                ],
                'notes' => [
                    'access_notes' => $property->access_notes,
                    'hazards' => $property->hazards,
                ],
            ]
        ])
    @endif

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header">Overview</div>
                <div class="card-body">
                    <div class="mb-2"><strong>Type:</strong> {{ ucfirst($property->type) }}</div>
                    <div class="mb-2"><strong>Status:</strong> {{ ucfirst($property->status) }}</div>
                    @if($property->property_code)
                        <div class="mb-2"><strong>Code:</strong> {{ $property->property_code }}</div>
                    @endif
                    @if($property->access_notes)
                        <div class="mb-2"><strong>Access:</strong><br>{!! nl2br(e($property->access_notes)) !!}</div>
                    @endif
                    @if($property->hazards)
                        <div class="mb-2"><strong>Hazards:</strong><br>{!! nl2br(e($property->hazards)) !!}</div>
                    @endif
                    @if($property->lockbox_code || $property->keys_location)
                        <div class="mb-2"><strong>Keys:</strong> {{ $property->keys_location }} @if($property->lockbox_code) (Lockbox: {{ $property->lockbox_code }}) @endif</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header">Primary contact</div>
                <div class="card-body">
                    <div class="mb-1"><strong>{{ $property->primary_contact_name ?? '-' }}</strong></div>
                    <div class="mb-1">{{ $property->primary_contact_phone ?? '' }}</div>
                    <div class="mb-1">{{ $property->primary_contact_email ?? '' }}</div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Quick links</div>
                <div class="card-body">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('managedpremises.properties.contacts.index', $property->id) }}">Contacts</a>
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('managedpremises.properties.units.index', $property->id) }}">Units</a>
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('managedpremises.properties.jobs.index', $property->id) }}">Jobs</a>
                </div>
            </div>
        </div>
    
    <div class="mt-3 d-flex flex-wrap gap-2">
        <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('managedpremises.properties.units.index', $property->id) }}">@lang('managedpremises::app.units')</a>
        <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('managedpremises.properties.contacts.index', $property->id) }}">@lang('managedpremises::app.contacts')</a>
        <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('managedpremises.properties.jobs.index', $property->id) }}">@lang('managedpremises::app.jobs')</a>
        <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('managedpremises.properties.keys.index', $property->id) }}">@lang('managedpremises::app.keys_access')</a>
        <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('managedpremises.properties.photos.index', $property->id) }}">@lang('managedpremises::app.photos')</a>
        <a class="btn btn-outline-primary btn-sm" href="{{ route('managedpremises.properties.checklists.index', $property->id) }}">@lang('managedpremises::app.checklists')</a>
    </div>

    {{-- Titan Zero (safe include) --}}
    <div class="mt-4">
        @includeIf('titanzero::partials.ask-titan', [
            'permission' => 'managedpremises.ai',
            'context' => [
                'module' => 'managedpremises',
                'page' => 'property.show',
                'company_id' => company()->id ?? null,
                'property_id' => $property->id,
                'property' => [
                    'name' => $property->name,
                    'address' => $property->address,
                    'access_notes' => $property->access_notes,
                    'hazards' => $property->hazards,
                ],
            ],
            'suggestions' => [
                'Summarise access notes and hazards',
                'Draft a work order checklist for this property',
                'Spot missing details (keys, lockbox, photos, exclusions)',
            ],
        ])
    </div>
</div>
@endsection
