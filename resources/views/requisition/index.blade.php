@extends('layouts.layout')
@section('title', 'Requisitions')

@section('scripts')

<script language="javascript">
$(document).ready(function(){
    //sort
    var order = $('[name=order]').val();
    var direction = $('[name=dir]').val();

    if (order && $('[data-column-name=' + order + ']').length) {
        $('[data-column-name=' + order + ']').addClass(direction);
    } else if ($('.rplus-order.asc, .rplus-order.desc').length) {
        //already ordered
    } else {
        $('.rplus-order').first().addClass('desc');
    }


    $('.rplus-order').click(function() {
        var columnName = $(this).data('column-name');
        var direction = 'asc';
        if ($(this).hasClass('asc')) {
            direction = 'desc';
        }
        $('[name=order]').val(columnName);
        $('[name=dir]').val(direction);
        $("#requisitionSearchForm").submit();
    });


    //datepicker
    $('input[name="created"],input[name="updated"]').daterangepicker({
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


    @can('admin')
    // Archive
    $('#archiveModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget) // Button that triggered the modal
        var checked = $('input[type=checkbox][name=select][value]').filter(':checked').map(function(){ return this.value; }).get().join();
        var archive = button.data('archive'); //action

        $(this).find('.archive_action').text( archive == true ? 'archive' : 'unarchive');
        $(this).find('input[name=archive_action]').val(archive);
        $(this).find('input[name=selected]').val(checked);
    });
    @endcan

});
</script>
@endsection

@section('top-content')
<div class="text-right">
<a href="#" class="btn btn-warning text-white btn-md" data-toggle='modal' data-target='#createRequisitionModal'><i class="fa fa-edit fa-lg"></i> Create Requisition</a>
</div>
@endsection
@section('content')

<div class="pb-3">
    <div class="btn-group" role="group">
        <a class="btn btn-{{$q == 'mine' ? '' : 'outline-' }}primary" href="{{ route('requisition', ['q' => 'mine', 'type' => $type]) }}">
            <i class="fa fa-caret-square-right"></i> My requisitions
        </a>
        <a class="btn btn-{{$q == 'confirmed' ? '' : 'outline-' }}primary" href="{{ route('requisition', ['q' => 'confirmed', 'type' => $type]) }}">
            <i class="fa fa-pen"></i> To be confirmed
            <span class="badge text-white badge-{{ array_sum($confirmedCount) == 0 ? 'secondary' : 'danger' }}">{{ array_sum($confirmedCount) }}</span>
        </a>
        <a class="btn btn-{{$q == 'delegating' ? '' : 'outline-' }}primary" href="{{ route('requisition', ['q' => 'delegating', 'type' => $type]) }}">
            <i class="fa fa-user-check"></i> Delegating
        </a>
        <a class="btn btn-{{$q == 'archived' ? '' : 'outline-' }}dark" href="{{ route('requisition', ['q' => 'archived', 'type' => $type]) }}">
            <i class="fa fa-file-archive"></i> Archive
        </a>
        <a class="btn btn-{{$q == 'all' ? '' : 'outline-' }}primary" href="{{ route('requisition', ['q' => 'all', 'type' => $type]) }}" checked>
            <i class="fa fa-file-signature"></i> All
        </a>
    </div>
</div>



@if ($q == 'archived')
<div class="alert alert-info text-center">
My archived requisitions (contact "ICT" to unarchive.)
</div>
@endif



<ul class="nav nav-tabs" role="tablist">
    <?php
        $tabs = [
            'procurements' => [],
            'purchases' => [],
            'orders' => ['disabled' => true, 'auth' => 'order'],
            'vouchers' => ['auth' => 'voucher'],
            'travels' => [],
            'subsistences' => ['auth' => 'subsistence'],
            'transports' => ['auth' => 'transport'],
            'maintenances' => ['disabled' => true],
            'bookings' => ['disabled' => true],
            'claims' => ['disabled' => true],
            'loans' => ['disabled' => true],
        ];
    ?>
    @foreach($tabs as $tabName => $tabAttr)
    @if(empty($tabAttr['disabled']) && (empty($tabAttr['auth']) || Auth::user()->can($tabAttr['auth'])))
    <li class="nav-item">
        <a class="nav-link {{ ($type == $tabName) ? 'active' : '' }}" href="{{ route('requisition', ['q' => $q, 'type' => $tabName]) }}" role="tab" aria-selected="{{ $type == $tabName }}">
            <i class="fa rplus-icon-{{ Str::singular($tabName) }}"></i> {{ ucfirst($tabName) }}
            @if($q == 'confirmed')
            <span class="badge text-white badge-{{ !empty($confirmedCount[$tabName]) ? 'danger' : 'secondary' }}">{{ !empty($confirmedCount[$tabName]) ?  $confirmedCount[$tabName] : 0 }}</span>
            @endif
        </a>
    </li>
    @endif
    @endforeach
</ul>

<div class="table-responsive">
    @if( $type == 'procurements' )
    @include("requisition.procurement_index")
    @elseif ( $type == 'purchases' )
    @include("requisition.purchase_index")
    @elseif( $type == 'travels' )
    @include("requisition.travel_index")
    @elseif( $type == 'subsistences' )
    @include("requisition.subsistence_index")
    @elseif( $type == 'transports' )
    @include("requisition.transport_index")
    @elseif ( $type == 'vouchers' )
    @include("requisition.voucher_index")
    @endif
</div>

<div class="row">
    <div class="col-md-6">
        {{ $requisitions->firstItem() }} - {{ $requisitions->lastItem() }}  / {{ $requisitions->total() }}
    </div>
    <div class="col-md-6">
        <div class="float-right">
            {{ $requisitions->appends(request()->input())->links() }}
        </div>
    </div>
</div>
@include('requisition.feedback.create_requisition_modal')
@endsection