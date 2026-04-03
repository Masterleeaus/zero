@php
    $editSubTaskPermission = user()->permission('edit_sub_tasks');
    $deleteSubTaskPermission = user()->permission('delete_sub_tasks');
    $addSubTaskPermission = user()->permission('add_sub_tasks');
    $viewSubTaskPermission = user()->permission('view_sub_tasks');
    $userId = ($userId != null) ? $userId : user()->id;
@endphp

@forelse ($service job->checklists as $checklist)
<div class="card w-100 rounded-0 border-0 checklist mb-1">

    <div class="card-horizontal">
        <div class="d-flex">
            <x-forms.checkbox :fieldId="'checkbox'.$checklist->id" class="service job-check"
                data-sub-service job-id="{{ $checklist->id }}"
                :checked="($checklist->status == 'complete') ? true : false" fieldLabel=""
                :fieldName="'checkbox'.$checklist->id" />

        </div>
        <div class="card-body pt-0">
            <div class="d-flex">
                @if ($checklist->assigned_to)
                    <x-cleaner-image :user="$checklist->assignedTo" />
                @endif

                <p class="card-title f-14 mr-3 text-dark flex-grow-1">
                    {!! $checklist->status == 'complete' ? '<s>' . $checklist->title . '</s>' : '<a class="view-checklist text-dark-grey" href="javascript:;" data-row-id=' . $checklist->id . ' >' .  $checklist->title . '</a>' !!}
                    {!! $checklist->due_date ? '<span class="f-11 text-lightest"><br>'.__('modules.invoices.due') . ': ' . $checklist->due_date->translatedFormat(company()->date_format) . '</span>' : '' !!}
                </p>
                <div class="dropdown ml-auto checklist-action">
                    <button
                        class="btn btn-lg f-14 p-0 text-lightest  rounded  dropdown-toggle"
                        type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-ellipsis-h"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                        aria-labelledby="dropdownMenuLink" tabindex="0">

                        @if ($viewSubTaskPermission == 'all' || ($viewSubTaskPermission == 'added' && ($checklist->added_by == user()->id || $checklist->added_by == $userId || in_array($checklist->added_by, $clientIds))))
                            <a class="dropdown-item view-checklist" href="javascript:;"
                                data-row-id="{{ $checklist->id }}">@lang('app.view')</a>
                        @endif

                        @if ($editSubTaskPermission == 'all' || ($editSubTaskPermission == 'added' && ($checklist->added_by == user()->id || $checklist->added_by == $userId || in_array($checklist->added_by, $clientIds))))
                            <a class="dropdown-item edit-checklist" href="javascript:;"
                                data-row-id="{{ $checklist->id }}">@lang('app.edit')</a>
                        @endif

                        @if ($deleteSubTaskPermission == 'all' || ($deleteSubTaskPermission == 'added' && ($checklist->added_by == user()->id || $checklist->added_by == $userId || in_array($checklist->added_by, $clientIds))))
                            <a class="dropdown-item delete-checklist" data-row-id="{{ $checklist->id }}"
                                href="javascript:;">@lang('app.delete')</a>
                        @endif
                    </div>
                </div>
            </div>


            @if (count($checklist->files) > 0)
                <div class="d-flex flex-wrap mt-4">
                    @foreach ($checklist->files as $file)
                        <x-file-card :fileName="$file->filename"
                            :dateAdded="$file->created_at->diffForHumans()">
                            <x-file-view-thumbnail :file="$file"></x-file-view-thumbnail>

                            <x-slot name="action">
                                <div class="dropdown ml-auto file-action">
                                    <button
                                        class="btn btn-lg f-14 p-0 text-lightest  rounded  dropdown-toggle"
                                        type="button" data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">
                                        <i class="fa fa-ellipsis-h"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                        aria-labelledby="dropdownMenuLink" tabindex="0">
                                        @if ($file->icon != 'images')
                                            <a class="cursor-pointer d-block text-dark-grey f-13 pt-3 px-3 "
                                                target="_blank"
                                                href="{{ $file->file_url }}">@lang('app.view')</a>
                                        @endif

                                        <a class="cursor-pointer d-block text-dark-grey f-13 py-3 px-3 "
                                            href="{{ route('sub-service job-files.download', md5($file->id)) }}">@lang('app.download')</a>

                                        @if (user()->id == $user->id)
                                            <a class="cursor-pointer d-block text-dark-grey f-13 pb-3 px-3 delete-sub-service job-file"
                                                data-row-id="{{ $file->id }}"
                                                href="javascript:;">@lang('app.delete')</a>
                                        @endif
                                    </div>
                                </div>
                            </x-slot>
                        </x-file-card>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</div>
@empty
    <x-cards.no-record :message="__('team chat.noSubTaskFound')" icon="service jobs" />
@endforelse
