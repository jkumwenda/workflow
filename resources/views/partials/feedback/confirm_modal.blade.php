<div class="modal fade" role="dialog" id="confirmModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Confirmation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">Are you sure you want to proceed?</div>
            <div class="modal-footer">
                {!! Form::open(['method' => 'post', 'id' => 'confirmForm']) !!}
                    {!! Form::button('Cancel', ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) !!}
                    {!! Form::button('<i class="fa fa-thumbs-up"></i> OK', ['type' => 'submit', 'class' => 'btn btn-success']) !!}
                {!! Form::close() !!}
              </div>
        </div>
    </div>
</div>
