@extends('layouts.layout')
@section('title', 'Preferences - Priority')

@section('scripts')
<script language="javascript">
</script>
@endsection

@section('top-content')
<div class="float-right">
    <a href="{{ route('priority.create') }}" class="text-white btn btn-warning"> <i class="fa fa-plus"></i> New Priority</a>
</div>
@endsection

@section('content')
<div class="table-responsive">
    <table class="table table-bordered table-hover datatable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Module</th>
                <th>Role</th>
                <th>Preferred USer</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            @foreach($priorities as $priority)
            <tr>
                <td><a href="{{ route('priority.show', $priority->id) }}">{{ $priority->id }}</a></td>
                <td>{{ $modules[$priority->module_id - 1] }}</td>
                <td>{{ $priority->role->description }}</td>
                <td>{{ $priority->user->full_name }}</td>
                <td style="width: 150px;">
                    <div class="btn-group" role="group">
                        <a href="{{ route('priority.edit', $priority->id) }}" class="text-white btn btn-warning btn-sm btn-just-icon"><i class="fa fa-edit"></i></a>
                        <a href="javascript:void(0);" class="btn btn-danger btn-sm btn-just-icon" data-item="{{ route('priority.destroy', $priority->id) }}" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash"></i></a>
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
