@php
    // Titan Talk context button
    // Inputs:
    //   $type  = 'client' | 'project' | 'ticket' | 'task' | 'invoice' | 'lead' | ...
    //   $id    = entity id
    //   $label = optional button label
    $param      = $type . '_id';
    $buttonText = $label ?? 'Titan Talk';
@endphp

<a href="{{ route('titantalk.conversations.index', [$param => $id]) }}"
   class="btn btn-sm btn-outline-primary ml-1"
   title="Open Titan Talk unified inbox for this {{ $type }}">
    <i class="fa fa-comments"></i> {{ $buttonText }}
</a>
