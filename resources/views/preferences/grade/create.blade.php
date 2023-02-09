@extends('layouts.layout')
@section('title', 'Preferences - Grade')

@section('scripts')
<script language="javascript">
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-9 m-xl-auto">
        {!! Form::open(['route' => 'grades.store', 'autocomplete' => 'off']) !!}
            <div class="card">
                <div class="card-header card-header-primary">
                    <h4 class="card-title">Create a Grade</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('name', 'Name') !!}
                                {!! Form::text('name', null, ['class' => 'form-control','required' => 'required']) !!}

                                {!! Form::label('subsistence', 'Subsistence') !!}
                                {!! Form::number('subsistence', null, ['class' => 'form-control','required' => 'required','step'=>'any','min'=>'0']) !!}

                                {!! Form::label('lunch', 'Lunch') !!}
                                {!! Form::number('lunch', null, ['class' => 'form-control','required' => 'required','step'=>'any','min'=>'0']) !!}
                                
                                {!! Form::label('international', 'International') !!}
                                {!! Form::number('international', null, ['class' => 'form-control','required' => 'required','step'=>'any','min'=>'0']) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <!-- Cancel -->
                    <a href="{{ route('grades.index') }}" class="btn btn-outline-dark"><i class="fa fa-backward"></i> Cancel</a>
                    <!-- Submit -->
                    {!! Form::button('<i class="fa fa-save"></i> Save', ['type' => 'submit', 'class' => 'btn btn-primary float-right']) !!}
                </div>
            </div>
        </div>
        {!! Form::close() !!}
</div><!-- Row -->
@endsection
