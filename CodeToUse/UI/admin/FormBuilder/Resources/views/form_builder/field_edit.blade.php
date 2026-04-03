
{{ Form::model($form_field, array('route' => array('form.field.update', $form->id, $form_field->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row" id="frm_field_data">
        <div class="col-6 form-group">
            {{ Form::label('name', __('Question Name'),['class'=>'col-form-label']) }}
            {{ Form::text('name', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('type', __('Type'),['class'=>'col-form-label']) }}
            {{ Form::select('type', $types,null, array('class' => 'form-control','required'=>'required')) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Close')}}</button>
    <button type="submit" class="btn  btn-primary">{{__('Update')}}</button>
</div>

{{ Form::close() }}

