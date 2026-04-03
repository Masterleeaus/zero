<div class="table-responsive">
    <table class="table table-sm align-middle">
        <thead>
            <tr>
                <th>{{ __('Timesheet::timesheet.reports.item') }}</th>
                <th class="text-end">{{ __('Timesheet::timesheet.reports.entries') }}</th>
                <th class="text-end">{{ __('Timesheet::timesheet.reports.total_hours') }}</th>
                <th class="text-end">{{ __('Timesheet::timesheet.reports.total_cost') }}</th>
            </tr>
        </thead>
        <tbody>
        @forelse($rows as $r)
            <tr>
                <td>
                    @if(($type ?? '') === 'work_order')
                        {{ $r['label'] ?? $r['work_order_id'] }}
                    @else
                        #{{ $r['project_id'] }}
                    @endif
                </td>
                <td class="text-end">{{ $r['entries'] }}</td>
                <td class="text-end">{{ $r['hours_decimal'] }}</td>
                <td class="text-end">{{ number_format($r['cost_total'], 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-muted">{{ __('Timesheet::timesheet.reports.none') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
