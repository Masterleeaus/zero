@php
$changeStatusPermission = user()->permission('change_status');
@endphp

@foreach ($service jobs as $service job)
    <x-cards.service job-card :service job="$service job" :draggable="($changeStatusPermission == 'all' ? 'true' : 'false')" :company="$company"/>
@endforeach
