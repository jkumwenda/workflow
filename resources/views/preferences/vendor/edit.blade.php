@extends('layouts.layout')
@section('title', 'Settings - Vendor')

@section('scripts')
<script language="javascript">
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-9 m-xl-auto">
        {!! Form::model($supplier, ['method' => 'PATCH', 'route' => ['vendors.update', $supplier->id], 'autocomplete' => 'off']) !!}
            <div class="card">
                <div class="card-header card-header-primary">
                    <h4 class="card-title">Update Vendor info</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('name', 'Name') !!}
                                {!! Form::text('name', null, ['class' => 'form-control','required' => 'required']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('email', 'Email') !!}
                                {!! Form::email('email', null, ['class' => 'form-control']) !!}
                            </div>
                        </div>
                        <div class="col-md-2">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('address', 'Address') !!}
                                {!! Form::textarea('address', null, ['class' => 'form-control']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('phone', 'Phone(s))') !!}
                                {!! Form::text('phone', null, ['class' => 'form-control']) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <!-- Cancel -->
                    <a href="{{ route('vendors.index') }}" class="btn btn-outline-dark"><i class="fa fa-backward"></i> Cancel</a>
                    <!-- Submit -->
                    {!! Form::button('<i class="fa fa-save"></i> Save', ['type' => 'submit', 'class' => 'btn btn-primary float-right']) !!}
                </div>
            </div>
        {!! Form::close() !!}
    </div>
</div><!-- Row -->
@endsection
