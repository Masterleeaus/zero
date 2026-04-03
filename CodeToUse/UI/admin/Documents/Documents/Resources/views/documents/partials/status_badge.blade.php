@php
  $st = $status ?? 'draft';
  $map = [
    'draft' => 'secondary',
    'review' => 'warning',
    'approved' => 'success',
    'archived' => 'dark',
  ];
@endphp
<span class="badge bg-{{ $map[$st] ?? 'secondary' }}">{{ strtoupper($st) }}</span>
