<x-forms.select fieldId="timer_task_id" fieldName="task_id" :fieldLabel="__('app.service job')" :fieldRequired="$fieldRequired"
    search="true">
    <option value="">--</option>
    @foreach ($service jobs as $item)
        <option @php
            $name = '';
            if (!is_null($item->project_id)) {
                $name .= '<h5 class="f-12 text-darkest-grey">' . $item->heading . '</h5><div class="text-muted f-11">' . $item->site->project_name . '</div>';
            } else {
                $name .= '<span class="text-dark-grey f-11">' . $item->heading . '</span>';
            }
        @endphp data-content="{{ $name }}" value="{{ $item->id }}">
            {{ $item->heading }}
        </option>
    @endforeach
</x-forms.select>
