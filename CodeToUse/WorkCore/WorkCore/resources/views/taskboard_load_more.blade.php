@foreach ($service jobs as $service job)
    <x-cards.public-service job-card :service job="$service job" :draggable="'false'"/>
@endforeach
