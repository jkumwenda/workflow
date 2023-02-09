@extends('layouts.layout')
@section('title', 'Settings - User')

@section('scripts')
<script language="javascript">
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-9 m-xl-auto">
        {!! Form::open(['route' => 'users.store', 'autocomplete' => 'off']) !!}
            <div class="card">
                <div class="card-header card-header-primary">
                    <h4 class="card-title">Create a User</h4>
                </div>
                <div class="card-body">
                <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('salutation', 'Salutation') !!}
                                {!! Form::select('salutation',['Prof' => 'Prof', 'Dr' => 'Dr', 'Mr' => 'Mr', 'Mrs' => 'Mrs', 'Miss' => 'Miss', 'M/S' => 'M/S'],null, ['class' => 'form-control','required' => 'required','placeholder' => '-- Please Select --']) !!}
                            </div>
                        </div>
                   </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('username', 'Username') !!}
                                {!! Form::text('username', null, ['class' => 'form-control','required' => 'required']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('email', 'Email') !!}
                                {!! Form::email('email', null, ['class' => 'form-control','required' => 'required']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('first_name', 'First Name') !!}
                                {!! Form::text('first_name', null, ['class' => 'form-control','required' => 'required']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('surname', 'Surname') !!}
                                {!! Form::text('surname', null, ['class' => 'form-control','required' => 'required']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('Phone Number', 'Phone Number') !!}
                                {!! Form::text('phone_number', null, ['class' => 'form-control','required' => 'required']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('campus_id', 'Campus') !!}
                                {!! Form::select('campus_id',$campuses,null, ['class' => 'form-control select2','required' => 'required','placeholder' => '-- Please Select --']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('role_ids[]', 'Role') !!}
                                {!! Form::select('role_ids[]',$roles,null, ['class' => 'form-control select2','required' => 'required','multiple'=>'multiple']) !!}
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('grade_id', 'Grade') !!}
                                {!! Form::select('grade_id',$grades,null, ['class' => 'form-control','required' => 'required','placeholder' => '-- Please Select --']) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <span id="flow-summary-popover"></span>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('unit_id', 'Unit / Department') !!}
                                {!! Form::select('unit_id',$units,null, ['class' => 'form-control select2','required' => 'required','placeholder' => '-- Please Select --']) !!}
                                <i>If user belongs other units/departments, please add them after creating.</i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <!-- Cancel -->
                    <a href="{{ route('roles.index') }}" class="btn btn-outline-dark"><i class="fa fa-backward"></i> Cancel</a>
                    <!-- Submit -->
                    {!! Form::button('<i class="fa fa-save"></i> Save', ['type' => 'submit', 'class' => 'btn btn-primary float-right']) !!}
                </div>
            </div>
        </div>
        {!! Form::close() !!}
</div><!-- Row -->
@endsection
