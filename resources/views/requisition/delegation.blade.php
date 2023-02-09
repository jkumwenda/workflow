@extends('layouts.layout')
@section('title', 'Requisition - Delegated tasks')

@section('scripts')

<script language="javascript">
$(document).ready(function(){

    //datepicker
    $('input[name="created"]').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        },
        ranges: {
        'Today': [moment(), moment()],
        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
        'This Year': [moment().startOf('year'), moment().endOf('year')],
        'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
        '2018 Year': [moment('2018/1/1'), moment('2018/12/31')],
        '2017 Year': [moment('2017/1/1'), moment('2017/12/31')],
        }
    });
    $('input[name="created"],input[name="updated"]').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    });

    $('input[name="created"],input[name="updated"]').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
});
</script>
@endsection

@section('top-content')
@endsection
@section('content')

<div class="pb-3 text-right">
    <div class="btn-group" role="group">
    <a class="btn btn-{{$status == '' ? '' : 'outline-' }}danger" href="{{ route('requisition/delegation', ['status' => '']) }}">
            <i class="fa fa-caret-square-right"></i> All
        </a>
        <a class="btn btn-{{$status == 'Pending' ? '' : 'outline-' }}danger" href="{{ route('requisition/delegation', ['status' => 'Pending']) }}">
            <i class="fa fa-pen"></i> Pending
        </a>
        <a class="btn btn-{{$status == 'Checked' ? '' : 'outline-' }}danger" href="{{ route('requisition/delegation', ['status' => 'Checked']) }}">
            <i class="fa fa-check"></i> Checked
        </a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Type</th>
                <th>#</th>
                <th>Requisition title</th>
                <th>Owner</th>
                <th>Department / Unit</th>
                <th>Status</th>
                <th>Date</th>
                <th>Sender</th>
                <th>Receiver</th>
                <th>Options</th>
            </tr>
        </thead>
        <tbody>
            <tr class="bg-light">
                {!! Form::open(['method' => 'GET', 'autocomplete' => 'off']) !!}
                    <th>{!! Form::select('type', addEmpty(['procurements' => 'Procurement', 'purchases' => 'Purchase']), Request::get('type'), ['class' => 'form-control ' . (Request::filled('type') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
                    <th>{!! Form::text('id', Request::get('id'), ['class' => 'form-control ' . (Request::filled('id') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
                    <th>{!! Form::text('title', Request::get('title'), ['class' => 'form-control ' . (Request::filled('title') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
                    <th>{!! Form::text('owner', Request::get('owner'), ['class' => 'form-control ' . (Request::filled('owner') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
                    <th>{!! Form::text('unit', Request::get('unit'), ['class' => 'form-control ' . (Request::filled('unit') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
                    <th>{!! Form::select('status', addEmpty(['Pending' => 'Pending', 'Checked' => 'Checked']), Request::get('status'), ['class' => 'form-control ' . (Request::filled('status') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
                    <th>{!! Form::text('created', Request::get('created'), ['class' => 'form-control ' . (Request::filled('created') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
                    <th>{!! Form::text('sender', Request::get('sender'), ['class' => 'form-control ' . (Request::filled('sender') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
                    <th>{!! Form::text('receiver', Request::get('receiver'), ['class' => 'form-control ' . (Request::filled('receiver') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
                    <th>
                        <span class="btn-group">
                            {{ Form::button('<i class="fa fa-search"></i>', ['type' => 'submit', 'class' => 'btn btn-outline-warning']) }}
                        </span>
                    </th>
                {!! Form::close() !!}
            </tr>
            @foreach($delegations as $delegation)
            <tr>
                <?php
                    $type = Str::singular($delegation->delegationable_type);
                    $id = $delegation->delegationable_id;

                    $delegationable = null;
                    if ($type == 'procurement') $delegationable = $delegation->delegationable;
                    else if ($type == 'purchase') $delegationable = $delegation->delegationable->procurement;
                ?>
                <td>{{ $type }}</td>
                <td>{{ idFormatter($type, $id) }}</td>
                <td><a href="{{ route($type . '/show', $id) }}">{{ $delegationable->title ?? 'No title' }}</a></td>
                <td>{{ $delegationable->createdUser->name }}</td>
                <td>{{ $delegationable->unit->name }}</td>
                <td>{{ $delegation->status }}</td>
                <td>{{ dateformat($delegation->created_at) }}</td>
                <td>
                    <div>{{ $delegation->sender->name }}</div>
                    @if (!empty($delegation->sender_comment))
                    <small class="trail-comment">{{ $delegation->sender_comment }}</small>
                    @endif
                </td>
                <td>
                    <div>{{ $delegation->receiver->name }}</div>
                    @if (!empty($delegation->receiver_comment))
                    <small class="trail-comment">{{ $delegation->receiver_comment }}</small>
                    @endif
                </td>
                <td >
                    <div class="btn-group" role="group">
                        <a href="{{ route($type . '/show', $id) }}" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="row">
    <div class="col-md-6">
        {{ $delegations->firstItem() }} - {{ $delegations->lastItem() }}  / {{ $delegations->total() }}
    </div>
    <div class="col-md-6">
        <div class="float-right">
            {{ $delegations->appends(request()->input())->links() }}
        </div>
    </div>
</div>
@endsection