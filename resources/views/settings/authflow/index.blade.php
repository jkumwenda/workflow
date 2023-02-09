@extends('layouts.layout')
@section('title', 'Settings - Auth flow')

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
        <h3>{{$company->name}}</h3>
        <hr>
        @foreach($modules as $parent => $children)
        <h4>{{$parent}}</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($children as $module_name =>  $module_id)
                <tr>
                    <td>{{ $module_id }}</td>
                    <td>{{ $module_name }}</td>
                    <td style="width: 150px;">
                        <div class="btn-group" role="group">
                            <a href="{{route('authflow.details',[$company->id, $module_id])}}" class="btn btn-primary btn-sm btn-just-icon"><i class="fa fa-exchange-alt"></i> Flow</a>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        @endforeach
 </div>
@include('partials.feedback.delete_modal')

<a href="{{ route('companies.index') }}" class="btn btn-outline-dark"><i class="fa fa-backward"></i> Back</a>

@endsection
