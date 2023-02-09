@extends('layouts.layout')
@section('title', 'Settings - Units')

@section('scripts')
<script language="javascript">
</script>
@endsection

@section('top-content')
<div class="float-right">
    <a href="{{ route('units.create') }}" class="text-white btn btn-warning"> <i class="fa fa-plus"></i> New Unit</a>
</div>
@endsection

@section('content')
<div class="table-responsive">
    <table class="table table-bordered table-hover datatable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Code</th>
                <th>Name</th>
                <th>Company</th>
                <th>Category</th>
                <th>Company DB</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            @foreach($units as $unit)
            <tr>
                <td>{{ $unit->id }}</td>
                <td>{{ $unit->code }}</td>
                <td><a href="{{ route('units.edit', $unit->id) }}">{{ $unit->name }}</a></td>
                <td>{{ $unit->company->name }}</td>
                <td>{{ $unit->category }}</td>
                <td>{{ $unit->db_name }}</td>
                <td style="width: 150px;">
                    <div class="btn-group" role="group">
                        <a href="{{ route('units.edit', $unit->id) }}" class="text-white btn btn-warning btn-sm btn-just-icon"><i class="fa fa-edit"></i></a>

                        <button class="btn btn-sm btn-outline-warning dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="true">
                            <span class="caret"></span>
                        </button>
                        <div class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                            <a href="{{ route('unit.users', $unit->id) }}" class="dropdown-item"><i class="fa fa-users"></i> Users and Roles</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="javascript:void(0);" data-item="{{ route('units.destroy', $unit->id) }}" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash"></i> Delete</a>

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
