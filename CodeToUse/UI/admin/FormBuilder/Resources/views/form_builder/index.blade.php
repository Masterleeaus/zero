@extends('layouts.main')

@section('page-title')
    {{__('Manage Forms')}}
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('.cp_link').on('click', function () {
                var value = $(this).attr('data-link');
                var $temp = $("<input>");
                $("body").append($temp);
                $temp.val(value).select();
                document.execCommand("copy");
                $temp.remove();
                toastrs('Success', '{{__('Link Copy on Clipboard')}}', 'success')
            });
        });
    </script>
@endpush

@section('page-action')
    <div class="row align-items-center m-1">
        <div class="col-auto pe-0">
            <a class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Create Form')}}" data-ajax-popup="true" data-size="md" data-title="{{__('Create Form')}}" data-url="{{route('form_builder.create')}}"><i class="ti ti-plus text-white"></i></a>
        </div>
    </div>

@endsection

@section('page-breadcrumb')
   {{__('Form Builder')}}
@endsection


@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 pc-dt-simple" id="assets">
                            <thead>
                                <tr>
                                    <th width="50%">{{__('Name')}}</th>
                                    <th width="25%">{{__('Response')}}</th>
                                    <th>{{__('Action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                               @foreach ($forms as $form)
                                    <tr>
                                        <td>{{ $form->name }}</td>
                                        <td>{{ $form->response->count() }}</td>
                                        <td class="Action">
                                             <div class="action-btn bg-primary ms-2">
                                                <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Click to copy link')}}" class="mx-3 btn btn-sm d-inline-flex align-items-center cp_link" data-link="{{url('/form/'.$form->code)}}" data-toggle="tooltip" data-original-title="{{__('Click to copy link')}}"><i class="ti ti-file text-white"></i></a>
                                             </div>

                                            <div class="action-btn bg-warning ms-2">
                                                <a href="{{route('form_builder.show',$form->id)}}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Edit/View Form field')}}" class="btn btn-icon btn-sm" data-toggle="tooltip" data-original-title="{{__('Edit/View Form field')}}"><i class="ti ti-table text-white"></i></a>
                                            </div>

                                            <div class="action-btn bg-warning ms-2">
                                                <a href="{{route('form.response',$form->id)}}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('View Response')}}" class="btn btn-icon btn-sm" data-toggle="tooltip" data-original-title="{{__('View Response')}}"><i class="ti ti-eye text-white"></i></a>
                                            </div>

                                            <div class="action-btn bg-success ms-2">
                                                <a class="btn btn-icon btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Convert into Lead Setting')}}"  data-url="{{ route('form.field.bind',$form->id) }}" data-ajax-popup="true" data-title="{{__('Convert into Lead Setting')}}" class="edit-icon bg-success" data-toggle="tooltip" data-original-title="{{__('Convert into Lead Setting')}}"><i class="ti ti-exchange text-white"></i></a>
                                            </div>

                                            <div class="action-btn bg-info ms-2">
                                                <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Edit Form')}}"  data-url="{{ URL::to('form_builder/'.$form->id.'/edit') }}" data-ajax-popup="true" data-title="{{__('Edit Form')}}" class="btn btn-icon btn-sm" ><i class="ti ti-pencil text-white"></i></a>
                                            </div>
                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['form_builder.destroy', $form->id]]) !!}
                                                    <a href="#!" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Delete Form')}}" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm">
                                                       <span class="text-white"> <i class="ti ti-trash"></i></span>
                                                {!! Form::close() !!}
                                            </div>
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
