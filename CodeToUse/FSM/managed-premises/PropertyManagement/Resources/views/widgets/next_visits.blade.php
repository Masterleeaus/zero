<div class="card">
    <div class="card-header">{{ __('propertymanagement::pm.next_visits') }}</div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th>{{ __('propertymanagement::pm.when') }}</th><th>{{ __('propertymanagement::pm.property') }}</th><th>{{ __('propertymanagement::pm.status') }}</th></tr></thead>
            <tbody>
            @forelse($pm_next_visits as $v)
                <tr>
                    <td>{{ optional($v->scheduled_for)->format('d M Y H:i') }}</td>
                    <td><a href="{{ route('propertymanagement.properties.show', $v->property_id) }}">{{ $v->property?->name ?? ('#'.$v->property_id) }}</a></td>
                    <td><span class="badge bg-secondary">{{ $v->status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted p-4">{{ __('propertymanagement::pm.none') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
