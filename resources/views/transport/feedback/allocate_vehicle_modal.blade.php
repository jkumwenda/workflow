
<div class="modal fade" id="allocate_vehicle_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"> Allocate vehicle and assign driver </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            {!! Form::open(['method' => 'POST', 'route' => ['transport/allocate',$transport->id ], 'autocomplete' => 'off']) !!}
                <div class="modal-body">
                    <fieldset>  
                        <div class="row">
                            <div class="col-md-12">  

                                <div class="alert alert-warning"><i class="fa fa-car fa-lg"></i> Allocate Vehicle </div>
                                  {!! Form::select('vehicle',$vehicles,$travel->vehicle_id, ['class' => 'form-control','required' => 'required','placeholder' => '-- Please Select --']) !!}
                                 <br>
                         
                                <div class="alert alert-warning"><i class="fa fa-user fa-lg"></i> Assign Driver </div>
                                {!! Form::select('driver',$drivers,$travel->driver_id, ['class' => 'form-control','required' => 'required','placeholder' => '-- Please Select --']) !!}
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    {!! Form::button('<i class="fa fa-paper-plane"></i> Allocate', ['type' => 'submit', 'class' => 'btn btn-danger']) !!}
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
