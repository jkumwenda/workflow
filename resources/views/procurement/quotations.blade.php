@extends('layouts.layout')
@section('title', 'Procurement Requisition Quotations')

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
                <h3>{{ $procurement->title }} - Quotations</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    {!! listSupportedDocuments($quotations, 'quote', true) !!}
                </div>
            </div>
        </div>

        <div class="card border-info mb-5">
            <div class="card-body text-info">
                <h3>Upload quotations</h3>
                <h5 class="text-danger mb-3"><i class="fa fa-exclamation-triangle"></i> A minimum of three quotations are required. Make sure the files are in PDF format.</h5>

                {!! Form::open(['method' => 'post', 'files'=>'true']) !!}
                    <div class="row">

                        {!! Form::hidden('document_type', 'Quotation') !!}
                        <div class="col-md-6">
                            <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" />

                            @error('file')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            {!! Form::button('<i class="fa fa-upload"></i> Upload', ['type' => 'submit', 'class' => 'btn btn-primary']) !!}
                        </div>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>

        <div>
            <a href="{{ route('procurement/show', $procurement->id) }}" class="btn btn-success"><i class="fa fa-check"></i> Finish </a>
        </div>

    </div>
</div>


@include('procurement.feedback.delete_document_modal')

@endsection