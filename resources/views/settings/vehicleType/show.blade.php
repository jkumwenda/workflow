@extends('layouts.layout')
@section('title', 'Settings - Vehicle')

@section('scripts')
<script language="javascript">
</script>
@endsection

@section('top-content')

@endsection

@section('content')

<h4>{{$vehicleTypes->name }}</h4>
<hr>
<div class="table-responsive">
    <table class="table table-bordered table-hover datatable">
        <thead>
            <tr>
                <th>Registration Number</th>
                <th>Make</th>   
                <th>Capacity</th>
                <th>Colour</th>
                <th>Unit</th>
                <th>Campus</th>
                
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
        @foreach($vehicleShows->vehicle as $vehicle)
            <tr>
                
                <td>{{ $vehicle->registration_number }}</td>
                <td>{{ $vehicle->make->make }}</td>
                <td>{{ $vehicle->capacity }}</td>
                <td>{{ $vehicle->colour }}</td>
                <td>{{ $vehicle->unit->name }}</td>
                <td>{{ $vehicle->campus->name }}</td>
                
                <td style="width: 150px;">
                    <div class="btn-group" role="group">
                        <a href="{{ route('vehicles.edit', $vehicle->id) }}" class="btn btn-primary btn-sm btn-just-icon"><i class="fa fa-edit"></i></a>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="true">
                                <span class="caret"></span>
                            </button>
                            <div class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                                <a class="dropdown-item" href="{{ route('authflow', $vehicle->id) }}"><i class="fa fa-exchange-alt"></i> Authorization Flows</a>
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
<a href="{{ route('vehicleTypes.index') }}" class="btn btn-outline-dark"><i class="fa fa-backward"></i> Back</a>
@endsection
