<!-- Confirm Reset PR -->
<div class="modal fade" id="confirm_reset_pr" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Confirm</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" id="text-delete">
            This action to reset the current purchase requisition. Are you sure you want to continue?
            </div>
            {!! Form::open(['method' => 'DELETE', 'route' => ['purchase/reset', $purchase->id], 'autocomplete' => 'off']) !!}
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                {!! Form::button('<i class="fa fa-trash"></i> Reset', ['type' => 'submit', 'class' => 'btn btn-danger']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>