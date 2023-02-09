
<div class="modal fade" id="allocate_vehicle_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Transport and Subsistance requestion</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            {!! Form::open(['method' => 'POST', 'route' => ['transport/approval', $travel->id], 'autocomplete' => 'off']) !!}
                <div class="modal-body">
                    <fieldset>
                        
                        <div class="row">
                            <div class="col-md-12">
                                 
                               <p>Your are about to create Transport and Subsistance requestion</p>
                            </div>
                        </div>
                    </fieldset>
                    {!! Form::hidden('travel_id', $travel->id) !!}
                    {!! Form::hidden('unit_id', $requisition->unit->id) !!}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    {!! Form::button('<i class="fa fa-paper-plane"></i> Create', ['type' => 'submit', 'class' => 'btn btn-danger']) !!}
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
