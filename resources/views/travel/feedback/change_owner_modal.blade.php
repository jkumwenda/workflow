<div class="modal fade" id="change_owner_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Change owner</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            {!! Form::open(['method' => 'POST', 'route' => ['procurement/changeOwner', $requisition->id], 'autocomplete' => 'off']) !!}
                <div class="modal-body">
                    <fieldset>
                        <label for="comment">Current Owner:</label>
                        <div class="row">
                            <div class="col-md-12 h5">
                                {{ $requisition->unit->name }}
                            </div>
                            <div class="col-md-12 h3">
                            {{ $requisition->createdUser->name }}
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <label for="comment">New Owner:</label>
                        <div class="row">
                            <div class="col-md-12">
                            {!! Form::select('new_owner_user_id', $unitUsers, null, ['class' => 'form-control form-control-lg font-weight-bold','required' => 'required', 'placeholder' => ' - Select - ']) !!}
                            </div>
                        </div>
                    </fieldset>


                    <fieldset>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="comment">Comment:</label>
                                {!! Form::textarea('comment', null, ['class' => 'form-control']) !!}
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
