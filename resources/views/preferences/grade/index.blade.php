@extends('layouts.layout')
@section('title', 'Preferences - Grade')

@section('scripts')
<script language="javascript">
</script>
@endsection

@section('top-content')
<div class="float-right">
    <a href="{{ route('grades.create') }}" class="text-white btn btn-warning"> <i class="fa fa-plus"></i> New Grade</a>
</div>
@endsection

@section('content')
<div class="table-responsive">
    <table class="table table-bordered table-hover datatable">
        <thead>
            <tr>
                <th>Name</th>
                <th>Subsistence</th>
                <th>Lunch</th>
                <th>International</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grades as $grade)
            <tr>
                <td><a href="{{ route('grades.show', $grade->id) }}">{{ $grade->name }}</a></td>
                <td>{{ $grade->subsistence }}</td>
                <td>{{ $grade->lunch }}</td>
                <td>{{ $grade->international }}</td>
                <td style="width: 150px;">
                    <div class="btn-group" role="group">
                        <a href="{{ route('grades.edit', $grade->id) }}" class="text-white btn btn-warning btn-sm btn-just-icon"><i class="fa fa-edit"></i></a>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-warning dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="true">
                                <span class="caret"></span>
                            </button>
                            <div class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                                <a class="dropdown-item" href="{{ route('grades.show', $grade->id) }}"><i class="fa fa-users"></i> Users</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="javascript:void(0);" class="btn btn-outline-danger btn-sm btn-just-icon" data-item="{{ route('grades.destroy', $grade->id) }}" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash"></i> Delete</a>
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
