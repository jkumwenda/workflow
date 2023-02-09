@extends('layouts.layout')
@section('title', 'Settings - User')

@section('scripts')
<script language="javascript">
</script>
@endsection

@section('top-content')
<div class="float-right">
    <a href="{{ route('users.create') }}" class="text-white btn btn-warning"> <i class="fa fa-plus"></i> New User</a>
    <a href="{{ route('user.import') }}" class="text-white btn btn-warning"> <i class="fa fa-plus"></i> Import Users</a>
</div>


@endsection

@section('content')
<div class="table-responsive">
    <table class="table table-bordered table-hover datatable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Salutation</th>
                <th>Username</th>
                <th>First Name</th>
                <th>Surname</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Campus</th>
                <th>Grade</th>
                <th>Active</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->salutation }}</td>
                <td><a href="{{ route('users.edit', $user->id) }}">{{ $user->username}}</a></td>
                <td>{{ $user->first_name }}</td>
                <td>{{ $user->surname }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->phone_number?? 'None'}}</td>
                <td> {{ $user->campus->name ?? 'None' }} </td>
                <td>{{ $user->grade->name ?? 'None' }}</td>
                <td>
                    {{ $user->active === "1" ? "Yes" : "No" }}
                    @if($user->active === "1")
                        <a href="{{ route('user.deactivate', $user->id) }}" class="btn btn-primary btn-sm btn-just-icon" data-toggle="tooltip" title="lock"><i class="fa fa-lock"> </i></a>
                    @else
                        <a href="{{ route('user.activate', $user->id) }}" class="btn btn-danger btn-sm btn-just-icon" data-toggle="tooltip" title="unlock"><i class="fa fa-unlock"></i></a>
                    @endif

                </td>
                <td style="width: 150px;">
                    <div class="btn-group" role="group">
                        <a href="{{ route('users.edit', $user->id) }}" class="text-white btn btn-warning btn-sm btn-just-icon"><i class="fa fa-edit"></i></a>

                        <button class="btn btn-sm btn-outline-warning dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="true">
                            <span class="caret"></span>
                        </button>
                        <div class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                            <a href="{{ route('user.unitRoles', $user->id) }}" class="dropdown-item"><i class="far fa-building"></i>Roles and Units</a>
                            <div class="dropdown-divider"></div>
                            <a href="javascript:void(0);" class="dropdown-item text-danger" data-item="{{ route('users.destroy', $user->id) }}" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash"></i> Delete</a>

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
