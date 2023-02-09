<div class="modal fade" role="dialog" id="deleteModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Confirmation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">Are you sure you want to delete the selected entry?</div>
            <div class="modal-footer">
                {!! Form::open(['method' => 'delete', 'id' => 'deleteForm']) !!}
                    {!! Form::button('Cancel', ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) !!}
                    {!! Form::button('<i class="fa fa-trash"></i> Proceed Delete', ['type' => 'submit', 'class' => 'btn btn-danger']) !!}
                {!! Form::close() !!}
              </div>
        </div>
    </div>
</div>
