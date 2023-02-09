@extends('layouts.layout')
@section('title', 'Preferences - Priority')

@section('scripts')
<script language="javascript">
</script>
@endsection
@section('top-content')
<div class="float-right">
    <a href="{{ route('priority.edit', $priority->id) }}" class="btn btn-primary"> <i class="fa fa-edit"></i> Edit</a>
    <a class="btn btn-danger" href="javascript:void(0);" data-item="{{ route('priority.destroy', $priority->id) }}" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash"></i> Delete</a>
</div>
@endsection

@section('content')
<h4>{{ $priority->name }}</h4>
<hr>
<div class="table-responsive">
    <table class="table table-bordered table-hover datatable">
        <thead>
            <tr>
                <th>Salutation</th>
                <th>User</th>
                <th>email</th>
                <th>Active</th>
            </tr>
        </thead>
        <tbody>
                @foreach ($priority->users as $user)
                <tr>
                    <td>{{ $user->salutation }}</td>
                    <td>{{ $user->first_name }} {{ $user->surname }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        {{ $user->active === "1" ? "Yes" : "No" }}
                        @if($user->active === "1")
                            <a href="{{ route('user.deactivate', $user->id) }}" class="btn btn-danger btn-sm btn-just-icon" data-toggle="tooltip" title="lock"><i class="fa fa-lock"> </i></a>
                        @else
                            <a href="{{ route('user.activate', $user->id) }}" class="btn btn-success btn-sm btn-just-icon" data-toggle="tooltip" title="unlock"><i class="fa fa-unlock"></i></a>
                        @endif
    
                    </td>
                    
                </tr>
                    
                @endforeach 
        </tbody>
    </table>
</div>

@include('partials.feedback.delete_modal')

<a href="{{ route('priority.index') }}" class="btn btn-outline-dark"><i class="fa fa-backward"></i> Back</a>
@endsection
