@extends('layouts.layout')
@section('title', 'Preferences - Vendors')

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
    <a href="{{ route('vendors.create') }}" class="text-white btn btn-warning"> <i class="fa fa-plus"></i> New Vendor</a>
</div>
@endsection

@section('content')
<div class="table-responsive">
    <table class="table table-bordered table-hover datatable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Address</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Score</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            @foreach($suppliers as $supplier)
            <tr>
                <td>{{ $supplier->id }}</td>
                <td>{{ $supplier->name }}</td>
                <td>{{ $supplier->address }}</td>
                <td>{{ $supplier->phone }}</td>
                <td>{{ $supplier->email }}</td>
                <td class="d-flex flex-row">
                    @if (!empty($supplier->score))
                    <span>{{ $supplier->score }}</span>
                    <input value="{{ $supplier->score }}" class="kv-ltr-theme-fa-star rating-loading rated-star" data-size="xs">
                    <span>(<a href="{{ route('vendors.comments', $supplier->id) }}">{{ $supplier->evaluation_number }}</a>)</span>
                    @else
                    <span>-</span>
                    @endif
                </td>
                <td style="width: 150px;">
                    <div class="btn-group" role="group">
                        <a href="{{ route('vendors.edit', $supplier->id) }}" class="text-white btn btn-warning btn-sm btn-just-icon"><i class="fa fa-edit"></i></a>
                        <a href="javascript:void(0);" class="btn btn-outline-danger btn-sm btn-just-icon" data-item="{{ route('vendors.destroy', $supplier->id) }}" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash"></i></a>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-warning dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="true">
                                <span class="caret"></span>
                            </button>
                            <div class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                                <a href="{{ route('vendors.edit', $supplier->id) }}" class="dropdown-item">Edit</a>
                                <a class="dropdown-item" href="{{ route('vendors.comments', $supplier->id) }}">See review comments</a>
                                <a class="dropdown-item" href="{{ route('requisition', ['type' => 'purchases', 'q' => 'all', 'supplier' => $supplier->name]) }}">See past purchases</a>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@include('partials.feedback.delete_modal')

@endsection
