@extends('layouts.layout')
@section('title', 'Procurement Requisition Supporting Documents')

@section('scripts')
<script language="javascript">
    $(document).ready(function(){
        $("#confirm_document_delete").on('show.bs.modal', function (e) {
            var button = e.relatedTarget;
            $(this).find("[name=document_id]").val($(button).data('document-id'));
        });
    });
</script>
@endsection

@section('top-content')
@endsection

@section('content')
<div class="row">
    <div class="col-md-10 m-auto" style="padding-bottom: 15px;">

        <!-- Header -->
        <div class="card bg-light mb-5">
            <div class="card-header">
                <h3>{{ idFormatter('procurement', $procurement->id) }} - [{{ $procurement->unit->name }}]</h3>
                <h3>{{ $procurement->title }} - Supporting documents</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    {!! listSupportedDocuments($documents, '', true) !!}
                </div>
            </div>
        </div>

        <div class="card border-info mb-5">
            <div class="card-body text-info">
                <h3>Upload supporting documents</h3>

                {!! Form::open(['method' => 'post', 'files'=>'true']) !!}
                    <div class="row">
                        <div class="col-md-3">
                            {!! Form::select('document_type', ['Quotation' => 'Quotation', 'Invoice' => 'Invoice', 'Misc' => 'Misc'], null, ['class' => 'form-control','required' => 'required', 'placeholder' => ' - Document type - ']) !!}
                        </div>
                        <div class="col-md-6">
                            <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" />

                            @error('file')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            {!! Form::button('<i class="fa fa-upload"></i> Upload', ['type' => 'submit', 'class' => 'btn btn-warning text-white']) !!}
                        </div>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>

        <div>
            <a href="{{ route('procurement/show', $procurement->id) }}" class="btn btn-warning text-white"><i class="fa fa-check"></i> Finish </a>
        </div>

    </div>
</div>


@include('procurement.feedback.delete_document_modal')

@endsection
