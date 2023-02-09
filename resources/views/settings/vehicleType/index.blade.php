@extends('layouts.layout')
@section('title', 'Settings - VehicleType')

@section('scripts')
<script language="javascript">
</script>
@endsection

@section('top-content')
<div class="float-right">
    <a href="{{ route('vehicleTypes.create') }}" class="text-white btn btn-warning"> <i class="fa fa-plus"></i> New VehicleType</a>
</div>
@endsection

@section('content')
<div class="table-responsive">
    <table class="table table-bordered table-hover datatable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>


            @foreach($vehicleTypes as $vehicleType)
            <tr>
                
                <td>{{ $vehicleType->id }}</td>
                <td><a href="{{ route('vehicleTypes.show', $vehicleType->id) }}">{{ $vehicleType->name }}</a></td>

                
                <td style="width: 150px;">
                    <div class="btn-group" role="group">
                        <a href="{{ route('vehicleTypes.edit', $vehicleType->id) }}" class="text-white btn btn-warning btn-sm btn-just-icon"><i class="fa fa-edit"></i></a>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-warning dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="true">
                                <span class="caret"></span>
                            </button>
                            <div class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">

                                <a class="dropdown-item text-danger" href="javascript:void(0);" class="btn btn-outline-danger btn-sm btn-just-icon" data-item="{{ route('vehicleTypes.destroy', $vehicleType->id) }}" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash"></i> Delete</a>
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
