
<div class="modal fade" id="allocate_driver_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Assign A Driver</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            {!! Form::open(['method' => 'POST', 'route' => ['travel/allocateDriver', $requisition->id], 'autocomplete' => 'off']) !!}
                <div class="modal-body">
                    <fieldset>
                        <div class="alert alert-warning"><i class="fa fa-car side"></i> Driver </div>
                        <div class="row">
                            <div class="col-md-12">
                                 {!! Form::label('driver', 'driver') !!}
                                {!! Form::select('driver_id',$drivers,null, ['class' => 'form-control','required' => 'required','placeholder' => '--------------------------------Please Select----------------------------------------']) !!}
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    {!! Form::button('<i class="fa fa-paper-plane"></i> Send', ['type' => 'submit', 'class' => 'btn btn-danger']) !!}
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
