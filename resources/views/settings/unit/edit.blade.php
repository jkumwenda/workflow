@extends('layouts.layout')
@section('title', 'Settings - Unit')

@section('scripts')
<script language="javascript">
$(function() {
    var companyFlowPopover = null;

    // show current summary flow
    getFlowSummary($('[name=company_id]').val());

    // show new summary flow
    $('[name=company_id]').change(function() {
        if (companyFlowPopover) {
            companyFlowPopover.popover('dispose');
        }
        var companyId = $(this).val();
        getFlowSummary(companyId);
    });

    $('#toggleFlow').click(function () {
        if (companyFlowPopover) {
            companyFlowPopover.popover('toggle');
        }
    });

    // get summary flow of selected company
    {{-- (/* there are same function in create.blade.php */) --}}
    function getFlowSummary(companyId) {
        $.ajax({
            url: "{{ route('authflow.summary') }}",
            data: {
                "_token": "{{ csrf_token() }}",
                "company_id": companyId
            },
            dataType: "json",
            method: "post",
            success: function(data) {
                console.log(data);

                var flows = [];
                for (moduleName in data.flowSummary) {
                    var table = '<table class="table"><thead><tr><td colspan="2">' + moduleName + '</td></tr></thead>';
                    table += '<thead>';

                    for (var i = 0; i < data.flowSummary[moduleName].length; i++) {
                        var fs = data.flowSummary[moduleName][i];
                        table += '<tr><td>' + (i+1) +'</td><td><strong>' + fs.role_name + '</strong><br/><small>' + fs.requisition_status_name + '</small></td></tr>';
                    }
                    table += '</thead></table>';
                    flows.push(table);
                }

                console.log(table);

                companyFlowPopover = $('#flow-summary-popover').popover({
                    container: 'body',
                    title: '<strong>' + data.company.name + '\'s flow</strong>',
                    content: function() {
                        var content = '<div class="d-inline-flex justify-content-center">';
                        for (var i = 0; i < flows.length; i++) {
                            content += flows[i];
                        }
                        return $(content);
                    },
                    html: true
                });
                companyFlowPopover.popover('update');
                companyFlowPopover.popover('show');

            }
        });
    }
});
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-9 m-xl-auto">
        {!! Form::model($unit, ['method' => 'PATCH', 'route' => ['units.update', $unit->id], 'autocomplete' => 'off']) !!}
            <div class="card">
                <div class="card-header card-header-primary">
                <a class="btn btn-sm btn-outline-secondary float-right" id="toggleFlow">Toggle company flow</a>
                    <h4 class="card-title">Update Unit info</h4>
                </div>
                <div class="card-body">
                <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('code', 'Code') !!}
                                {!! Form::text('code', null, ['class' => 'form-control','required' => 'required','placeholder' => '-- Please Select --']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('name', 'Name') !!}
                                {!! Form::text('name', null, ['class' => 'form-control','required' => 'required','placeholder' => '-- Please Select --']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('company_id', 'Company') !!}
                                {!! Form::select('company_id',$companies,null, ['class' => 'form-control','required' => 'required']) !!}
                                <strong class="text-danger">Note: Changing company will disrupt old requisition workflow.</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <span id="flow-summary-popover"></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('category', 'Category') !!}
                                {!! Form::select('category',['PROJECT' => 'PROJECT', 'MAIN' => 'MAIN'],null, ['class' => 'form-control','required' => 'required']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('db_name', 'Company DB') !!}
                                {!! Form::text('db_name', null, ['class' => 'form-control','required' => 'required']) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <!-- Cancel -->
                    <a href="{{ route('units.index') }}" class="btn btn-outline-dark"><i class="fa fa-backward"></i> Cancel</a>
                    <!-- Submit -->
                    {!! Form::button('<i class="fa fa-save"></i> Save', ['type' => 'submit', 'class' => 'btn btn-primary float-right']) !!}
                </div>
            </div>
        {!! Form::close() !!}
    </div>
</div><!-- Row -->
@endsection
