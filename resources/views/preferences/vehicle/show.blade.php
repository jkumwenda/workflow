@extends('layouts.layout')
@section('title', 'Settings - Vehicle')

@section('scripts')
<script language="javascript">
</script>
@endsection

@section('top-content')

@endsection

@section('content')

<h4>{{$vehicle->registration_number  }}</h4>
<hr>
<div class="table-responsive">
    <table class="table table-bordered table-hover datatable">
        <thead>
            <tr>
                <th>Registration Number</th>
                <th>Make</th>
                <th>Mileage</th>
                <th>Capacity</th>
                <th>Colour</th>
                <th>Vehicle Type</th>
                <th>Campus</th>
            </tr>
        </thead>
        <tbody>

            <tr>

                <td>{{ $vehicle->registration_number }}</td>
                <td>{{ $vehicle->make->make }}</td>
                <td>{{ $vehicle->mileage }}</td>
                <td>{{ $vehicle->capacity }}</td>
                <td>{{ $vehicle->colour }}</td>
                <td>{{ $vehicle->vehicleType->name }}</td>
                <td>{{ $vehicle->campus->name }}</td>


            </tr>

        </tbody>
    </table>
</div>

@include('partials.feedback.delete_modal')
<a href="{{ route('vehicles.index') }}" class="btn btn-outline-dark"><i class="fa fa-backward"></i> Back</a>
@endsection
