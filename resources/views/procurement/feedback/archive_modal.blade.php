<div class="modal fade" role="dialog" id="archiveModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Archive/Unarchive Confirmation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">Are you sure you want to <span class="archive_action text-danger h4">archive</span> the selected entry?</div>
            <div class="modal-footer">
                {!! Form::open(['method' => 'post', 'route' => 'procurement/archive', 'id' => 'archiveForm']) !!}
                    {!! Form::hidden('selected') !!}
                    {!! Form::hidden('archive_action') !!}

                    {!! Form::button('Cancel', ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) !!}
                    {!! Form::button('<i class="fa file-archive"></i> Proceed', ['type' => 'submit', 'class' => 'btn btn-danger']) !!}
                {!! Form::close() !!}
              </div>
        </div>
    </div>
</div>