@extends('layouts.layout')
@section('title', 'Settings - User Roles')

@section('scripts')
<script language="javascript">
</script>
@endsection
@section('top-content')
<div class="float-right">
    <a href="{{ route('user.addRole', $user->id) }}" class="btn btn-primary"> <i class="fa fa-plus"></i> Add User Role</a>
</div>
@endsection

@section('content')
<h4>{{$user->first_name}} {{$user->surname}}</h4>
<hr>
<div class="table-responsive">
    <table class="table table-bordered table-hover datatable">
        <thead>
            <tr>
                <th>Unit</th>
                <th>Roles</th>
                <th>Default</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($user->units as $unit)
            <tr>
                <td><a href="{{ route('unit.users', $unit->id) }}" data-toggle="tooltip" data-placement="top" title="See this unit's all users">{{ $unit->name }}</a></td>
                <td>{{ \App\Role::find($unit->pivot->role_id)->description }}</td>
                <td>
                    @if ($unit->pivot->is_default == "1")
                    <span class="badge badge-danger">Default Unit</span>
                    @else
                    <a href="{{ route('user.changeDefault', [$user->id, $unit->id]) }}">(Change default unit to this)</a>
                    @endif
                </td>
                <td>
                <a href="javascript:void(0);" class="btn btn-outline-danger btn-sm btn-just-icon" data-item="{{ route('user.deleteRole', [$user->id,$unit->id,$unit->pivot->role_id]) }}" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash"></i></a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

@include('partials.feedback.delete_modal')

<a href="{{ route('users.index') }}" class="btn btn-outline-dark"><i class="fa fa-backward"></i> Back</a>
@endsection
