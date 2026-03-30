<div class="d-flex flex-wrap gap-2 mb-3">
    <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('managedpremises.properties.show', $property->id) }}">@lang('managedpremises::app.overview')</a>
    <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('managedpremises.properties.units.index', $property->id) }}">@lang('managedpremises::app.units')</a>
    <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('managedpremises.properties.contacts.index', $property->id) }}">@lang('managedpremises::app.contacts')</a>
    <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('managedpremises.properties.jobs.index', $property->id) }}">@lang('managedpremises::app.jobs')</a>
    <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('managedpremises.properties.keys.index', $property->id) }}">@lang('managedpremises::app.keys_access')</a>
    <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('managedpremises.properties.photos.index', $property->id) }}">@lang('managedpremises::app.photos')</a>
    <a class="btn btn-outline-primary btn-sm" href="{{ route('managedpremises.properties.checklists.index', $property->id) }}">@lang('managedpremises::app.checklists')</a>
    <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('managedpremises.properties.tags.index', $property->id) }}">@lang('managedpremises::app.tags')</a>
    <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('managedpremises.properties.rooms.index', $property->id) }}">@lang('managedpremises::app.rooms')</a>
    <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('managedpremises.properties.hazards.index', $property->id) }}">@lang('managedpremises::app.hazards')</a>
    <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('managedpremises.properties.servicewindows.index', $property->id) }}">@lang('managedpremises::app.service_windows')</a>
    <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('managedpremises.properties.meters.index', $property->id) }}">@lang('managedpremises::app.meter_readings')</a>
    <a class="btn btn-outline-primary btn-sm" href="{{ route('managedpremises.properties.assets.index', $property->id) }}">@lang('managedpremises::app.assets')</a>
</div>