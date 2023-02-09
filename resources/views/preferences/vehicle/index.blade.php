@extends('layouts.layout')
@section('title', 'Preferences - Vehicles')

@section('scripts')
<script language="javascript">
</script>
@endsection

@section('top-content')
<div class="float-right">
    <a href="{{ route('vehicles.create') }}" class="text-white btn btn-warning"> <i class="fa fa-plus"></i> New Vehicle</a>
</div>
@endsection

@section('content')
<div class="table-responsive">
    <table class="table table-bordered table-hover datatable">
        <thead>
            <tr>
                <th>Registration Number</th>
                <th>Make</th>
                <th>Capacity</th>
                <th>Vehicle Type</th>
                <th>Campus</th>
                <th>Unit</th>

                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vehicles as $vehicle)
            <tr>

                <td>{{ $vehicle->registration_number }}</td>
                <td><a href="{{ route('vehicles.edit', $vehicle->id) }}">{{ $vehicle->make->make }}</a></td>
                <td>{{ $vehicle->capacity }}</td>
                <td>{{ $vehicle->vehicleType->name }}</td>
                <td>{{ $vehicle->campus->name }}</td>
                <td>{{ $vehicle->unit->name ?? 'POOL VEHICLE' }}</td>

                <td style="width: 150px;">
                    <div class="btn-group" role="group">
                        <a href="{{ route('vehicles.edit', $vehicle->id) }}" class="text-white btn btn-warning btn-sm btn-just-icon"><i class="fa fa-edit"></i></a>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-warning dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="true">
                                <span class="caret"></span>
                            </button>

                            <div class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                            <a href="{{ route('vehicles.show', $vehicle->id)}}" class="dropdown-item"><i class="fa fa-card"></i>Details</a>
                            <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="javascript:void(0);" class="btn btn-outline-danger btn-sm btn-just-icon" data-item="{{ route('vehicles.destroy', $vehicle->id) }}" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash"></i> Delete</a>
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
