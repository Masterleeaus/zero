
{{ Form::open(array('route' => ['form.field.store',$formbuilder->id])) }}
<div class="modal-body">
    <div class="row" id="frm_field_data">
        <div class="col-6 form-group">
            {{ Form::label('name', __('Question Name'),['class'=>'col-form-label']) }}
            {{ Form::text('name[]', '', array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('type', __('Type'),['class'=>'col-form-label']) }}
            {{ Form::select('type[]', $types,null, array('class' => 'form-control','required'=>'required','id'=>'type')) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Close')}}</button>
    <button type="submit" class="btn  btn-primary">{{__('Create')}}</button>
</div>

{{ Form::close() }}

