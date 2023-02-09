@extends('layouts.layout')
@section('title', 'Settings - Auth flow')

@section('scripts')
<script language="javascript">
$(function() {
    var itemTable = $('table').itemTable();
    $('tbody').sortable({
        stop: function(event, ui) {
            itemTable.setRowNumber();
        }
    });
});
</script>
@endsection

@section('top-content')
@endsection

@section('content')

 <div class="container">
        <h3>{{$company->name}} - {{$moduleName}}</h3>
        <hr>
        <strong><i>(*) Drag to sort order</i></strong>

        <div class="table-responsive">
            {!! Form::open(['method' => 'POST', 'autocomplete' => 'off']) !!}
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Stage (*)</th>
                            <th>Role </th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($flowDetails as $flowDetail)
                    <tr>
                        <td class='js-row-no'>{{ $flowDetail->level }}</td>
                        <td>{!! Form::select('role_id[]',$roles, $flowDetail->role_id, ['class' => 'form-control','placeholder' => '(None)']) !!}</td>
                        <td>{!! Form::select('requisition_status_id[]',$requisitionStatuses, $flowDetail->requisitionStatus->id, ['class' => 'form-control', 'required' => 'required', 'placeholder' => '(None)']) !!}</td>
                        {!! Form::hidden('old_flow_detail_id[]',$flowDetail->id) !!}
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="card-footer">
                    <!-- Cancel -->
                    <a href="{{ route('authflow', $company->id) }}" class="btn btn-outline-dark"><i class="fa fa-backward"></i> Cancel</a>
                    <!-- Submit -->
                    {!! Form::button('<i class="fa fa-save"></i> Save', ['type' => 'submit', 'class' => 'btn btn-primary float-right']) !!}
                </div>
            {!! Form::close() !!}
        </div>
 </div>
@include('partials.feedback.delete_modal')

@endsection
