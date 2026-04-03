@csrf
<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">{{ __('Timesheet::timesheet.fields.date') }}</label>
        <input type="date" name="date" class="form-control" value="{{ old('date', isset($timesheet) ? optional($timesheet->date)->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">{{ __('Timesheet::timesheet.fields.hours') }}</label>
        <input type="number" name="hours" class="form-control" min="0" max="24" value="{{ old('hours', $timesheet->hours ?? 0) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">{{ __('Timesheet::timesheet.fields.minutes') }}</label>
        <input type="number" name="minutes" class="form-control" min="0" max="59" value="{{ old('minutes', $timesheet->minutes ?? 0) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">{{ __('Timesheet::timesheet.fields.type') }}</label>
        <select name="type" class="form-select">
            @php $type = old('type', $timesheet->type ?? 'regular'); @endphp
            <option value="regular" @selected($type==='regular')>{{ __('Timesheet::timesheet.types.regular') }}</option>
            <option value="overtime" @selected($type==='overtime')>{{ __('Timesheet::timesheet.types.overtime') }}</option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ __('Timesheet::timesheet.fields.project') }}</label>
        <input type="number" name="project_id" class="form-control" value="{{ old('project_id', $timesheet->project_id ?? '') }}" placeholder="ID">
        <div class="form-text">{{ __('Timesheet::timesheet.hints.project') }}</div>
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('Timesheet::timesheet.fields.task') }}</label>
        <input type="number" name="task_id" class="form-control" value="{{ old('task_id', $timesheet->task_id ?? '') }}" placeholder="ID">
        <div class="form-text">{{ __('Timesheet::timesheet.hints.task') }}</div>
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('Timesheet::timesheet.fields.work_order') }}</label>
        <select name="work_order_id" id="timesheet_work_order_id" class="form-select">
            @if(old('work_order_id', $timesheet->work_order_id ?? null))
                <option value="{{ old('work_order_id', $timesheet->work_order_id ?? null) }}" selected>
                    {{ __('Timesheet::timesheet.hints.work_order_selected') }} #{{ old('work_order_id', $timesheet->work_order_id ?? null) }}
                </option>
            @else
                <option value="">{{ __('Timesheet::timesheet.hints.work_order_none') }}</option>
            @endif
        </select>
        <div class="form-text">{{ __('Timesheet::timesheet.hints.work_order') }}</div>
    </div>
</div>

    <div class="col-12">
        <label class="form-label">{{ __('Timesheet::timesheet.fields.notes') }}</label>
        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $timesheet->notes ?? '') }}</textarea>
    </div>
</div>


@push('scripts')
<script>
(function(){
  const sel = document.getElementById('timesheet_work_order_id');
  if(!sel) return;

  let lastQuery = '';
  let timer = null;

  function fetchOptions(q){
    const formData = new FormData();
    formData.append('q', q || '');
    // include project_id if present
    const project = document.querySelector('input[name="project_id"]');
    if(project && project.value) formData.append('project_id', project.value);

    fetch("{{ route('timesheet.lookups.work_orders') }}", {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
      },
      body: formData
    }).then(r => r.json()).then(res => {
      const data = (res && res.data) ? res.data : [];
      const current = sel.value;
      sel.innerHTML = '';
      sel.appendChild(new Option("{{ __('Timesheet::timesheet.hints.work_order_none') }}", ''));
      data.forEach(item => {
        const opt = new Option(item.label, item.id);
        if(String(item.id) === String(current)) opt.selected = true;
        sel.appendChild(opt);
      });
    }).catch(() => {});
  }

  // Simple type-to-search using select's built-in search on many browsers.
  sel.addEventListener('keydown', function(e){
    // build query from typing (letters/numbers)
    if(e.key.length === 1){
      lastQuery += e.key;
      clearTimeout(timer);
      timer = setTimeout(() => { lastQuery=''; }, 750);
      fetchOptions(lastQuery);
    }
    if(e.key === 'Backspace'){
      lastQuery = lastQuery.slice(0,-1);
      fetchOptions(lastQuery);
    }
  });

  // initial load
  fetchOptions('');
})();
</script>
@endpush
