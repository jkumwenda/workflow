<div class="modal fade" id="signature_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Signature</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            {!! Form::open(['method' => 'POST', 'autocomplete' => 'off', 'id' => 'signatureForm']) !!}
                <div class="modal-body">
                    Please sign in the dotted area
                    <div class="signature-wrapper">
                        <canvas id="signature-pad" class="signature-pad" width=400 height=200></canvas>
                    </div>
                    <div class="mt-2">
                        <a class="btn btn-sm btn-outline-primary" id="undo">Undo</a>
                        <a class="btn btn-sm btn-outline-danger" id="clear">Clear</a>
                    </div>
                    {!! Form::hidden('signature', null) !!}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    {!! Form::button('<i class="fa fa-save"></i> Save', ['type' => 'submit', 'class' => 'btn btn-danger']) !!}
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
