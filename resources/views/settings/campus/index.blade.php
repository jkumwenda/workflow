@extends('layouts.layout')
@section('title', 'Settings - VehicleType')

@section('scripts')
<script language="javascript">
</script>
@endsection

@section('top-content')
<div class="float-right">
    <a href="{{ route('campuses.create') }}" class="text-white btn btn-warning"> <i class="fa fa-plus"></i> New Campus</a>
</div>
@endsection

@section('content')
<div class="table-responsive">
    <table class="table table-bordered table-hover datatable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Campus Name</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            @foreach($campuses as $campus)
            <tr>
                <td>{{ $campus->id }}</td>
                <td><a href="{{ route('campuses.edit', $campus->id) }}">{{ $campus->name }}</a></td>
                <td style="width: 150px;">
                    <div class="btn-group" role="group">
                        <a href="{{ route('campuses.edit', $campus->id) }}" class="text-white btn btn-warning btn-sm btn-just-icon"><i class="fa fa-edit"></i></a>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-warning dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="true">
                                <span class="caret"></span>
                            </button>
                            <div class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                                <a class="dropdown-item" href="{{ route('authflow', $campus->id) }}"><i class="fa fa-exchange-alt"></i> Authorization Flows</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="javascript:void(0);" class="btn btn-outline-danger btn-sm btn-just-icon" data-item="{{ route('campuses.destroy', $campus->id) }}" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash"></i> Delete</a>
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
