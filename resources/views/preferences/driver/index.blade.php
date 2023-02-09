@extends('layouts.layout')
@section('title', 'Preferences - Driver')

@section('scripts')
<script language="javascript">
</script>
@endsection

@section('content')
<div class="table-responsive">
    <table class="table table-bordered table-hover datatable">
        <thead>
            <tr>
                <th>Username</th>
                <th>First Name</th>
                <th>Surname</th>
                <th>Email</th>
                <th>Campus</th>
                <th>Active</th>  
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td><a href="{{ route('users.edit', $user->id) }}">{{ $user->username}}</a></td>
                <td>{{ $user->first_name }}</td>
                <td>{{ $user->surname }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    {{ $user->campus->name ?? 'None' }}
                </td>
                <td>
                    {{ $user->active === "1" ? "Yes" : "No" }}
                    @if($user->active === "1")
                        <a href="{{ route('user.deactivate', $user->id) }}" class="btn btn-primary btn-sm btn-just-icon" data-toggle="tooltip" title="lock"><i class="fa fa-lock"> </i></a>
                    @else
                        <a href="{{ route('user.activate', $user->id) }}" class="btn btn-danger btn-sm btn-just-icon" data-toggle="tooltip" title="unlock"><i class="fa fa-unlock"></i></a>
                    @endif

                </td>
              
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@include('partials.feedback.delete_modal')

@endsection
