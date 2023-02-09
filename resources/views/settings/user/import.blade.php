@extends('layouts.layout')
@section('title', 'Import Users')

@section('scripts')
    <script language="javascript">
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-9 m-xl-auto">
            {!! Form::open(['method' => 'post','route' => 'user.upload', 'files'=>'true']) !!}
            <div class="card">
                <div class="card-header card-header-primary">
                    <h4 class="card-title">Import Users</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">

                            <div class="col-md-6">
                                <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" />

                                @error('file')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <!-- Cancel -->
                    <a href="{{ route('users.index') }}" class="btn btn-outline-dark"><i class="fa fa-backward"></i> Cancel</a>
                    <!-- Submit -->
                    {!! Form::button('<i class="fa fa-save"></i> Upload', ['type' => 'submit', 'class' => 'btn btn-primary float-right']) !!}
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div><!-- Row -->
@endsection
