@extends('layouts.layout')
@section('title', 'Settings - Role')

@section('scripts')
<script language="javascript">
</script>
@endsection

@section('top-content')
<div class="float-right">
    <a href="{{ route('roles.create') }}" class="text-white btn btn-warning"> <i class="fa fa-plus"></i> New Role</a>
</div>
@endsection

@section('content')
<div class="table-responsive">
    <table class="table table-bordered table-hover datatable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Short Name</th>
                <th>Description</th>
                <th>Single User</th>
                <th>Search Level</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            @foreach($roles as $role)
            <tr>
                <td>{{ $role->id }}</td>
                <td><a href="{{ route('roles.edit', $role->id) }}">{{ $role->short_name }}</a></td>
                <td><a href="{{ route('roles.edit', $role->id) }}">{{ $role->description }}</a></td>
                <td>{{ $role->single_user === "1" ? "Yes" : "No" }}</td>
                <td>{{ $role->search_level }}</td>
                <td style="width: 150px;">
                    <div class="btn-group" role="group">
                        <a href="{{ route('roles.edit', $role->id) }}" class="text-white btn btn-warning btn-sm btn-just-icon"><i class="fa fa-edit"></i></a>
                        <button class="btn btn-sm btn-outline-warning dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="true">
                            <span class="caret"></span>
                        </button>
                        <div class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                            <a href="{{ route('rolePermission', $role->id) }}" class="dropdown-item"><i class="fa fa-ban"></i> Permissions</a>
                            <a href="{{ route('roles.user', $role->id) }}" class="dropdown-item"><i class="fa fa-users"></i> Members</a>
                            <div class="dropdown-divider"></div>
                            <a href="javascript:void(0);" class="dropdown-item text-danger" data-item="{{ route('roles.destroy', $role->id) }}" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash"></i> Delete</a>

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
