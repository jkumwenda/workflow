<div class="modal fade" id="sendMessageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Send message</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            {!! Form::open(['method' => 'POST', 'route' => 'message/send', 'autocomplete' => 'off', 'id' => 'sendMessageForm']) !!}
                <div class="modal-body">
                    <fieldset>
                        <legend class="text-info">To</legend>
                        <div class="row">
                            <div class="col-md-12">
                                <span class="name h3"></span><br/>
                                {!! Form::hidden('receiver', null) !!}
                                <span class="description"> </span><br/><br/>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="comment">Message:</label>
                                {!! Form::textarea('question', null, ['class' => 'form-control']) !!}
                            </div>
                        </div>
                    </fieldset>

                    {!! Form::hidden('messageable_type', null) !!}
                    {!! Form::hidden('messageable_id', null) !!}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    {!! Form::button('<i class="fa fa-paper-plane"></i> Send', ['type' => 'submit', 'class' => 'btn btn-danger']) !!}
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>