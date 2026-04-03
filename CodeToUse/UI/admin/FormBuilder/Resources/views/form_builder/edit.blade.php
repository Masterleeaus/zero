
{{ Form::model($formBuilder, array('route' => array('form_builder.update', $formBuilder->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12 form-group">
            {{ Form::label('name', __('Name'),['class'=>'col-form-label']) }}
            {{ Form::text('name', null, array('class' => 'form-control','required' => 'required')) }}
        </div>
        <div class="col-12 form-group">
            {{ Form::label('active', __('Active'),['class'=>'col-form-label']) }}
            <div class="d-flex radio-check">
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="on" value="1" name="is_active" class="form-check-input" {{($formBuilder->is_active == 1) ? 'checked' : ''}}>
                    <label class="custom-control-label form-check-labe" for="on">{{__('On')}}</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline" style="margin-left: 10px;">
                    <input type="radio" id="off" value="0" name="is_active" class="form-check-input" {{($formBuilder->is_active == 0) ? 'checked' : ''}}>
                    <label class="custom-control-label form-check-labe" for="off">{{__('Off')}}</label>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Close')}}</button>
    <button type="submit" class="btn  btn-primary">{{__('Update')}}</button>
</div>

{{ Form::close() }}

