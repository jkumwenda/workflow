@extends('layouts.layout')
@section('title', 'Settings - Unit users')

@section('scripts')
<script language="javascript">
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-9 m-xl-auto">
        {!! Form::open(['route' => ['unit.attachUser', $unit->id], 'autocomplete' => 'off']) !!}
        {{ Form::hidden('unit_id', $unit->id) }}
            <div class="card">
                <div class="card-header card-header-primary">
                    <h4 class="card-title">{{ $unit->name }} - Add User</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('role_id', 'Role') !!}
                                {!! Form::select('role_id',$roles,null, ['class' => 'form-control select2','required' => 'required','placeholder' => '-- Please Select --']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('user_id', 'User') !!}
                                {!! Form::select('user_id',$users,null, ['class' => 'form-control select2','required' => 'required','placeholder' => '-- Please Select --']) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <!-- Cancel -->

                    <a href="{{ URL::previous() }}" class="btn btn-outline-dark"><i class="fa fa-backward"></i> Cancel</a>
                    <!-- Submit -->
                    {!! Form::button('<i class="fa fa-save"></i> Save', ['type' => 'submit', 'class' => 'btn btn-primary float-right']) !!}
                </div>
            </div>
        </div>
        {!! Form::close() !!}
</div><!-- Row -->
@endsection
