@extends('layouts.layout')
@section('title', 'Signature')

@section('scripts')
<script language="javascript">
$(document).ready(function(){
    /*******************
    * Signature
    ******************* */
    var signaturePad = null;
    $("#signature_modal").on('shown.bs.modal', function (e) {
        var canvas = document.getElementById('signature-pad');

        function resizeCanvas() {
            // When zoomed out to less than 100%, for some very strange reason,
            // some browsers report devicePixelRatio as less than 1
            // and only part of the canvas is cleared then.
            var ratio =  Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
        }

        window.onresize = resizeCanvas;
        resizeCanvas();

        if (signaturePad == null) {
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)'
            });

            $('#clear').on('click', function () {
                signaturePad.clear();
            });

            $('#undo').on('click', function() {
                var data = signaturePad.toData();
                if (data) {
                    data.pop(); // remove the last dot or line
                    signaturePad.fromData(data);
                }
            });
        }
    });

    //create purchase requisition
    $("#signatureForm").submit(function() {
        $("#error-alert").hide();
        submitCanceled = false;

        if (signaturePad.isEmpty()) {
            $("#error-alert").html("Please provide a signature first");
            $("#error-alert").show();
            submitCanceled = true;
            $('#signature_modal').modal('hide');
            return false;
        }

        var data = signaturePad.toDataURL();
        console.log(data);
        $('[name="signature"]').val(data);

        return true;
    });
});
</script>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                @if (empty(Auth::user()->signature))
                No signature
                @else
                <img height="200" src="{!! str_replace(' ', '+', Auth::user()->signature) !!}" />
                @endif
            </div>
            <div class="card-footer">
                <a href="{{ route('userSetting') }}" class="btn btn-outline-secondary"><i class="fa fa-backward"></i> Back</a>
                @if (empty(Auth::user()->signature))
                <a href="javascript:void(0);" data-toggle='modal' data-target='#signature_modal' class="btn btn-primary float-right"><i class="fa fa-plus"></i> Add signature</a>
                @else
                <a href="javascript:void(0);" data-toggle='modal' data-target='#signature_modal' class="btn btn-primary float-right ml-2"><i class="fa fa-plus"></i> Change signature</a>
                <a href="javascript:void(0);" data-toggle='modal' data-target='#delete_signature_modal' class="btn btn-danger float-right ml-2"><i class="fa fa-trash"></i> Delete</a>
                @endif
            </div>
        </div>
    </div>
</div>

@include('user_setting.feedback.signature_modal')
@include('user_setting.feedback.delete_signature_modal')

@endsection

