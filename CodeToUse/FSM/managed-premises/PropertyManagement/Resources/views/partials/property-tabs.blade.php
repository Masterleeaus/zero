<div class="d-flex flex-wrap gap-2 mb-3">
    <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('propertymanagement.properties.show', $property->id) }}">@lang('propertymanagement::app.overview')</a>
    <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('propertymanagement.properties.units.index', $property->id) }}">@lang('propertymanagement::app.units')</a>
    <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('propertymanagement.properties.contacts.index', $property->id) }}">@lang('propertymanagement::app.contacts')</a>
    <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('propertymanagement.properties.jobs.index', $property->id) }}">@lang('propertymanagement::app.jobs')</a>
    <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('propertymanagement.properties.keys.index', $property->id) }}">@lang('propertymanagement::app.keys_access')</a>
    <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('propertymanagement.properties.photos.index', $property->id) }}">@lang('propertymanagement::app.photos')</a>
    <a class="btn btn-outline-primary btn-sm" href="{{ route('propertymanagement.properties.checklists.index', $property->id) }}">@lang('propertymanagement::app.checklists')</a>
    <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('propertymanagement.properties.tags.index', $property->id) }}">@lang('propertymanagement::app.tags')</a>
    <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('propertymanagement.properties.rooms.index', $property->id) }}">@lang('propertymanagement::app.rooms')</a>
    <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('propertymanagement.properties.hazards.index', $property->id) }}">@lang('propertymanagement::app.hazards')</a>
    <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('propertymanagement.properties.servicewindows.index', $property->id) }}">@lang('propertymanagement::app.service_windows')</a>
    <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('propertymanagement.properties.meters.index', $property->id) }}">@lang('propertymanagement::app.meter_readings')</a>
    <a class="btn btn-outline-primary btn-sm" href="{{ route('propertymanagement.properties.assets.index', $property->id) }}">@lang('propertymanagement::app.assets')</a>
</div>