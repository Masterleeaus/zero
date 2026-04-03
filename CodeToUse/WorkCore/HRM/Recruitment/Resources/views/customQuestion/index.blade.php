@extends('layouts.main')

@section('page-title')
    {{ __('Manage Custom Question for interview') }}
@endsection
@section('page-breadcrumb')
   {{ __('Custom Question') }}
@endsection

@section('page-action')
<div>
    @permission('custom question create')
        <a  data-url="{{ route('custom-question.create') }}" data-ajax-popup="true"
            data-title="{{ __('Create New Custom Question') }}" data-bs-toggle="tooltip" title=""
            class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endpermission
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table mb-0 pc-dt-simple" id="assets">
                        <thead>
                            <tr>
                                <th>{{ __('Question') }}</th>
                                <th>{{ __('Is Required?*') }}</th>
                                @if (Laratrust::hasPermission('custom question edit') || Laratrust::hasPermission('custom question delete'))
                                <th width="200px">{{ __('Action') }}</th>
                            @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($questions as $question)
                                <tr>
                                    <td>{{ $question->question }}</td>
                                    <td>
                                        @if ($question->is_required == 'yes')
                                            <span
                                                class="badge bg-success p-2 px-3 rounded">{{ Modules\Recruitment\Entities\CustomQuestion::$is_required[$question->is_required] }}</span>
                                        @else
                                            <span
                                                class="badge bg-danger p-2 px-3 rounded">{{ Modules\Recruitment\Entities\CustomQuestion::$is_required[$question->is_required] }}</span>
                                        @endif
                                    </td>
                                    <td class="Action">
                                        <span>
                                            @permission('custom question edit')
                                                <div class="action-btn bg-info ms-2">
                                                    <a  class="mx-3 btn btn-sm  align-items-center"
                                                        data-url="{{ route('custom-question.edit', $question->id) }}"
                                                        data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title=""
                                                        data-title="{{ __('Edit Custom Question') }}"
                                                        data-bs-original-title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                            @endpermission

                                            @permission('custom question delete')
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['custom-question.destroy', $question->id], 'id' => 'delete-form-' . $question->id]) !!}
                                                    <a  class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm"
                                                        data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                        aria-label="Delete"><i
                                                            class="ti ti-trash text-white text-white"></i></a>
                                                    </form>
                                                </div>
                                            @endpermission
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
