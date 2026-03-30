@extends('layouts.app')

@section('pageTitle', $pageTitle ?? __('Workflows'))

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">{{ $pageTitle ?? __('Workflows') }}</h1>
        <a href="{{ route('workflow.create') }}" class="btn btn-primary">
            <i class="fa fa-plus-circle"></i> @lang('Add Workflow')
        </a>
    </div>

    <x-cards.data :title="__('workflowList')" padding="true">
        @if ($workflows->isEmpty())
            <x-cards.no-record icon="workflow" :message="__('noRecordFound')" :link="route('workflow.create')" :linkText="__('modules.workflow.addWorkflow')" />
        @else
            <table class="table table-bordered table-striped" id="workflow-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>@lang('Name')</th>
                        <th>@lang('Description')</th>
                        <th>@lang('Project Category')</th>
                        <th>@lang('Action')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($workflows as $workflow)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $workflow->name }}</td>
                            <td>{{ $workflow->description }}</td>
                            <td>{{ $workflow->project_category_id ?? __('Not Assigned') }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <!-- Edit Button -->
                                    <a href="{{ route('workflow.edit', $workflow->id) }}" class="btn btn-warning btn-sm">
                                        <i class="fa fa-edit"></i> @lang('app.edit')
                                    </a>
                                    <!-- Delete Button with SweetAlert -->
                                    <button type="button" class="btn btn-danger btn-sm delete-button ml-2" data-id="{{ $workflow->id }}">
                                        <i class="fa fa-trash"></i> @lang('app.delete')
                                    </button>
                                    <!-- Hidden Delete Form -->
                                    <form id="delete-form-{{ $workflow->id }}" action="{{ route('workflow.destroy', $workflow->id) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-cards.data>
</div>
@endsection

@push('scripts')
    <!-- Include DataTables -->
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert Library -->

    <script>
        $(document).ready(function () {
            $('#workflow-table').DataTable({
                responsive: true,
                autoWidth: false,
                ordering: true,
                info: true,
                paging: true,
                lengthChange: true,
                searching: true,
                language: {
                    search: "@lang('modules.workflow.searchPlaceholder')"
                }
            });

            // SweetAlert Delete Confirmation
            $('.delete-button').on('click', function () {
                let workflowId = $(this).data('id');
                let form = $('#delete-form-' + workflowId);

                Swal.fire({
                    title: "@lang('Are you sure?')",
                    text: "@lang('This action cannot be undone.')",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "@lang('Yes, delete it!')",
                    cancelButtonText: "@lang('Cancel')",
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-secondary'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
