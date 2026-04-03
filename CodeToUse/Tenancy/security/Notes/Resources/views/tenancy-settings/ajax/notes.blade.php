<div class="table-responsive p-20">
    <x-table class="table-bordered">
        <x-slot name="thead">
            <th>#</th>
            <th>@lang('trnotes::app.menu.remark')</th>
            <th>@lang('trnotes::app.menu.module')</th>
            <th>@lang('trnotes::app.menu.table')</th>
            <th class="text-right">@lang('app.action')</th>
        </x-slot>

        @forelse($notes as $key => $note)
            <tr id="cat-{{ $note->id }}">
                <td>{{ $key + 1 }}</td>

                <td data-row-id="{{ $note->id }}" contenteditable="true" class="edit-remark">
                    {{ mb_ucwords($note->remark) }}</td>
                <td>
                    <select class="form-control select-picker height-35 f-14" name="module_name" id="edit_module"
                        data-live-search="true" data-row-id="{{ $note->id }}">
                        <option value="{{ $note->module_name }}"> {{ ucwords($note->module_name) }}</option>
                    </select>
                </td>
                <td>
                    <select class="form-control select-picker height-35 f-14" name="bsType" id="edit_table"
                        data-live-search="true" data-row-id="{{ $note->id }}">
                        <option @if ('tr_access_card' == $note->table_name) selected @endif value="tr_access_card"> Card Access
                        </option>
                        <option @if ('guestbooks' == $note->table_name) selected @endif value="guestbooks"> Guest Books
                        </option>
                        <option @if ('tenan_parkir' == $note->table_name) selected @endif value="tenan_parkir"> Parking
                            Subscription</option>
                        <option @if ('tr_in_out_permit' == $note->table_name) selected @endif value="tr_in_out_permit"> Transfer
                            Permission</option>
                        <option @if ('tenan_parcel' == $note->table_name) selected @endif value="tenan_parcel"> Receiving Parcel
                            / Pick Up Parcel</option>
                        <option @if ('workpermits' == $note->table_name) selected @endif value="workpermits"> Work Permit</option>
                    </select>
                </td>
                <td class="text-right">
                    <x-forms.button-secondary data-cat-id="{{ $note->id }}" icon="trash" class="delete-category">
                        @lang('app.delete')
                    </x-forms.button-secondary>
                </td>
            </tr>
        @empty
            <x-cards.no-record-found-list colspan="5" />
        @endforelse
    </x-table>
</div>

<script>
    $('.delete-category').click(function() {
        const id = $(this).data('cat-id');
        let url = "{{ route('notes.destroy', ':id') }}";
        url = url.replace(':id', id);

        const token = "{{ csrf_token() }}";
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.recoverRecord')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('messages.confirmDelete')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary'
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {
                        '_token': token,
                        '_method': 'DELETE'
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            $('#cat-' + id).fadeOut();
                        }
                    }
                });
            }
        });

    });

    $('.edit-remark').focus(function() {
        $(this).data("initialText", $(this).html());
    }).blur(function() {
        if ($(this).data("initialText") !== $(this).html()) {
            let id = $(this).data('row-id');
            let value = $(this).html();
            let url = "{{ route('notes.update', ':id') }}";
            url = url.replace(':id', id);

            const token = "{{ csrf_token() }}";
            $.easyAjax({
                url: url,
                container: '#row-' + id,
                type: "POST",
                data: {
                    'remark': value,
                    '_token': token,
                    '_method': 'PUT'
                },
                blockUI: true,
                success: function(response) {
                    if (response.status == "success") {
                        window.location.reload();
                    }
                }
            })
        }
    });

    $(document).on('change', '#edit_module', function() {
        const id = $(this).data('row-id');
        const module_name = $(this).val();
        if (id == undefined) {
            return false;
        }
        let url = "{{ route('notes.update', ':id') }}";
        url = url.replace(':id', id);
        const token = "{{ csrf_token() }}";
        $.easyAjax({
            url: url,
            type: "POST",
            data: {
                'module_name': module_name,
                '_token': token,
                '_method': 'PUT'
            },
            blockUI: true,
            success: function(response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            }
        })
    });

    $(document).on('change', '#edit_table', function() {
        const id = $(this).data('row-id');
        const table_name = $(this).val();
        if (id == undefined) {
            return false;
        }
        let url = "{{ route('notes.update', ':id') }}";
        url = url.replace(':id', id);
        const token = "{{ csrf_token() }}";
        $.easyAjax({
            url: url,
            type: "POST",
            data: {
                'table_name': table_name,
                '_token': token,
                '_method': 'PUT'
            },
            blockUI: true,
            success: function(response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            }
        })
    });
</script>
