@extends('layouts.layout')
@section('title', 'Settings - Unit users')

@section('scripts')
<script language="javascript">
</script>
@endsection
@section('top-content')
<div class="float-right">
</div>
@endsection

@section('content')
<h4>{{$role->description}} ({{ $role->short_name }})</h4>
<hr>
<div class="table-responsive">
    <table class="table table-bordered table-hover datatable">
        <thead>
            <tr>
                <th>User</th>
                <th>Unit</th>
                <th>Default</th>
                <th>Active User</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($role->users as $user)
            <tr>
                <td><a href="{{route('user.unitRoles', $user->id)}}" data-toggle="tooltip" data-placement="top" title="See this user's all units">{{ $user->name }}</a></td>
                <td>{{ \App\Unit::find($user->pivot->unit_id)->name }}</td>
                <td>
                    @if ($user->pivot->is_default == "1")
                    <span class="badge badge-danger">Default Unit</span>
                    @endif
                </td>
                <td>{{ $user->active === "1" ? "Yes" : "No" }}</td>
                <td>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

@include('partials.feedback.delete_modal')

<a href="{{ route('roles.index') }}" class="btn btn-outline-dark"><i class="fa fa-backward"></i> Back</a>
@endsection
