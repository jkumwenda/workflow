@extends('layouts.layout')
@section('title', 'Settings - Role Permission')

@section('scripts')
<script language="javascript">
</script>
@endsection

@section('top-content')
<div class="float-right">

</div>
@endsection

@section('content')

 <div class="container">
        <h3>{{ $role->description }}</h3>
        <hr>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                {!! Form::open(['method' => 'POST', 'autocomplete' => 'off']) !!}
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Permission Name</th>
                            <th>Description</th>
                            <th>Permission</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($permissions as $permission)
                    <tr>
                        <td>{{ $permission->id }}</td>
                        <td>{{ $permission->name }}</td>
                        <td>{{ $permission->description }}</td>
                        <td>
                            {{ $role->permissions()->where('id', $permission->id)->exists() ? 'YES' : '' }}
                            <input type="checkbox" name="permissions[]" class="form-control" value="{{ $permission->id }}" {{ $role->permissions()->where('id', $permission->id)->exists() ? 'checked=checked' : '' }}>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="card-footer">
                    <!-- Cancel -->
                    <a href="{{ route('roles.index') }}" class="btn btn-outline-dark"><i class="fa fa-backward"></i> Cancel</a>
                    <!-- Submit -->
                    {!! Form::button('<i class="fa fa-save"></i> Save', ['type' => 'submit', 'class' => 'btn btn-primary float-right']) !!}
                </div>
            {!! Form::close() !!}

        </div>

 </div>

@endsection
