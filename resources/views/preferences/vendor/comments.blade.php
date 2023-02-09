@extends('layouts.layout')
@section('title', 'Preferences - Supplier - Comments')

@section('scripts')
<script language="javascript">
$(function() {
    // Rating
    $('.rated-star').rating({
        displayOnly: true,
        theme: 'krajee-fas',
        showCaption: false,
    });

    $datatable = $('.datatable').DataTable();
    $datatable.on('preDraw preInit', function() {
        // Rating
        $('.rated-star').rating({
            displayOnly: true,
            theme: 'krajee-fas',
            showCaption: false,
        });
    });
})
</script>
@endsection

@section('top-content')
<div class="float-right">
    <a href="{{ route('vendors.create') }}" class="btn btn-primary"> <i class="fa fa-plus"></i> New Vendor</a>
</div>
@endsection

@section('content')
<div class="table-responsive">
    <div class="mb-2">
        <h2>{{ $supplier->name }}</h2>
        <h3 class="d-flex">{{ $supplier->score }} <input value="{{ $supplier->score }}" class="kv-ltr-theme-fa-star rating-loading rated-star" data-size="sm"> ({{ $supplier->evaluation_number }})</h3>

    </div>

    <table class="table table-bordered table-hover datatable">
        <thead>
            <tr>
                <th>Purchase ID</th>
                <th>Title</th>
                <th>Reviewer</th>
                <th>Score</th>
                <th>Comment</th>
            </tr>
        </thead>
        <tbody>
            @foreach($supplierEvaluations as $supplierEvaluation)
            <tr>
                <td><a href="{{ route('purchase/show', $supplierEvaluation->purchase->id)}}">{{ idFormatter('purchase', $supplierEvaluation->purchase->id) }}</a></td>
                <td>{{ $supplierEvaluation->purchase->procurement->title }}</td>
                <td>{{ $supplierEvaluation->createdUser->name }}</td>
                <td class="d-flex flex-row">
                    <span>{{ $supplierEvaluation->score }}</span>
                    <input value="{{ $supplierEvaluation->score }}" class="kv-ltr-theme-fa-star rating-loading rated-star" data-size="xs">
                </td>
                <td>{!! nl2br($supplierEvaluation->comment) !!}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Cancel -->
    <a href="{{ route('vendors.index') }}" class="btn btn-outline-dark"><i class="fa fa-backward"></i> Back</a>

</div>

@include('partials.feedback.delete_modal')

@endsection
