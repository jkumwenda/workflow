<div class="modal fade" id="answerMessageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Reply message</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            {!! Form::open(['method' => 'POST', 'route' => 'message/answer', 'autocomplete' => 'off', 'id' => 'answerMessageForm']) !!}
                <div class="modal-body">
                    <fieldset>
                        <legend class="text-info">To</legend>
                        <div class="row">
                            <div class="col-md-12">
                                <span class="name h3"></span><br/>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="text-info">Question</legend>
                        <div class="row">
                            <div class="col-md-12">
                                <span class="question h5"></span><br/>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="comment">Reply:</label>
                                {!! Form::textarea('answer', null, ['class' => 'form-control']) !!}
                            </div>
                        </div>
                    </fieldset>

                    {!! Form::hidden('message_id', null) !!}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    {!! Form::button('<i class="fa fa-paper-plane"></i> Send', ['type' => 'submit', 'class' => 'btn btn-danger']) !!}
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>