@extends('layouts.layout')
@section('title', 'Settings - Unit users')

@section('scripts')
<script language="javascript">
</script>
@endsection
@section('top-content')
<div class="float-right">
    <a href="{{ route('unit.addUser', $unit->id) }}" class="btn btn-primary"> <i class="fa fa-plus"></i> Add User</a>
</div>
@endsection

@section('content')
<h4>{{$unit->name}}</h4>
<hr>
<div class="table-responsive">
    <table class="table table-bordered table-hover datatable">
        <thead>
            <tr>
                <th>User</th>
                <th>Roles</th>
                <th>Default</th>
                <th>Active User</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($unit->users as $user)
            <tr>
                <td><a href="{{route('user.unitRoles', $user->id)}}" data-toggle="tooltip" data-placement="top" title="See this user's all units">{{ $user->name }}</a></td>
                <td>{{ \App\Role::find($user->pivot->role_id)->description }}</td>
                <td>
                    @if ($user->pivot->is_default == "1")
                    <span class="badge badge-danger">Default Unit</span>
                    @endif
                </td>
                <td>{{ $user->active === "1" ? "Yes" : "No" }}</td>
                <td>
                <a href="javascript:void(0);" class="btn btn-outline-danger btn-sm btn-just-icon" data-item="{{ route('unit.deleteUser', [$unit->id,$user->id,$user->pivot->role_id]) }}" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash"></i></a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

@include('partials.feedback.delete_modal')

<a href="{{ route('units.index') }}" class="btn btn-outline-dark"><i class="fa fa-backward"></i> Back</a>
@endsection
