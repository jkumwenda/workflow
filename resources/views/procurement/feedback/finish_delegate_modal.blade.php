<div class="modal fade" id="confirm_finish_delegate" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Send message (Submit task)</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            {!! Form::open(['method' => 'POST', 'route' => ['procurement/finishDelegate', $procurement->id], 'autocomplete' => 'off']) !!}
                <div class="modal-body">
                    <div class="alert alert-info">
                        The message will be sent to the one who delegated the task via email and system messages.
                    </div>
                    Are you sure want to submit task and send message?

                    <fieldset>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="comment">Message:</label>
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