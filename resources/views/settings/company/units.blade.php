@extends('layouts.layout')
@section('title', 'Settings')

@section('scripts')
<script language="javascript">
</script>
@endsection

@section('top-content')
<div class="float-right">
    
</div>
@endsection

@section('content')
<div>
    <h4>Units under {{$company->name}}</h4>
    <hr>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-hover datatable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Code</th>
                <th>Name</th>
                <th>Company</th>
                <th>Category</th>
                <th>Company DB</th>
            </tr>
        </thead>
        <tbody>
            @foreach($units as $unit)
            <tr>
                <td>{{ $unit->id }}</td>
                <td>{{ $unit->code }}</td>
                <td>{{ $unit->name }}</td>
                <td>{{ $unit->company->name }}</td>
                <td>{{ $unit->category }}</td>
                <td>{{ $unit->db_name }}</td>
    
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@include('partials.feedback.delete_modal')

@endsection
