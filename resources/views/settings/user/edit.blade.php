@extends('layouts.layout')
@section('title', 'Settings - User')

@section('scripts')
<script language="javascript">
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-9 m-xl-auto">
        {!! Form::model($user, ['method' => 'PATCH', 'route' => ['users.update', $user->id], 'autocomplete' => 'off']) !!}
            <div class="card">
                <div class="card-header card-header-primary">
                    <h4 class="card-title">Update User info</h4>
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
                                {!! Form::label('surname', 'Surname') !!}
                                {!! Form::text('surname', null, ['class' => 'form-control','required' => 'required']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('campus_id', 'Campus') !!}
                                {!! Form::select('campus_id',$campuses,null, ['class' => 'form-control','required' => 'required','placeholder' => '-- Please Select --']) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <!-- Cancel -->
                    <a href="{{ route('users.index') }}" class="btn btn-outline-dark"><i class="fa fa-backward"></i> Cancel</a>
                    <!-- Submit -->
                    {!! Form::button('<i class="fa fa-save"></i> Update', ['type' => 'submit', 'class' => 'btn btn-primary float-right']) !!}
                </div>
            </div>
        {!! Form::close() !!}
    </div>
</div><!-- Row -->
@endsection
