@php
  $dir = $call->direction ?? '';
  $st = $call->status ?? '';
  $missed = !empty($call->missed_at);
@endphp

@if($missed)
  <span class="titanhello-badge titanhello-badge--missed">Missed</span>
@elseif($dir==='outbound')
  <span class="titanhello-badge titanhello-badge--outbound">Outbound</span>
@else
  <span class="titanhello-badge titanhello-badge--answered">Inbound</span>
@endif

<span class="ms-2 titanhello-muted">{{ $st }}</span>