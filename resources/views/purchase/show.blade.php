@extends('layouts.layout')
@section('title', 'Purchase Requisition Detail')

@section('scripts')
<script language="javascript">
    $(document).ready(function(){
        // set LPO number
        $("#set_lpo_number").click(function() {
            $("#lpo").show("fast");
        });
        // Rating
        $('#rated-star').rating({
            displayOnly: true,
            theme: 'krajee-fas',
            showCaption: false,
        });
        $("#rateSupplierModal").on('shown.bs.modal', function (e) {
            $('#supplier-rating').rating({
                hoverOnClear: false,
                step: 1,
                theme: 'krajee-fas'
            });
        });
    });
</script>
@endsection

@section('top-content')
@if($procurement->archived)
<span class="badge-pill badge-secondary float-right"><i class="fa fa-file-archive"></i> Archived</span>
@endif
@endsection

@section('content')
<div class="row">
    <div class="col-xl-9 col-lg-8 " style="padding-bottom: 15px;">


        <!-- Header -->
        <div>
            <div class="card bg-light">
                <div class="card-header">
                    <h4>Purchase # - {{ idFormatter('purchase', $purchase->id) }}</h4>
                    <h3>{{ $procurement->title }}</h3>
                </div>
                <div class="card-body">
                    <h4><span class="text-primary">Department/Project:</span> {{ $procurement->unit->name }}</h4>
                    <h5><span class="text-primary">Owner:</span> {{ $procurement->createdUser->name }}</h5>
                    <h4><span class="text-primary">Selected supplier:</span> {{ $purchase->supplier->name }} - {{ $purchase->route }}</h4>
                    <span class="float-right">( Purchase created on: {{ $purchase->created_at }} )<span>
                </div>
            </div>
            <table class="table table-bordered">
                <tr>
                    <td class="pb-1 pt-1" width="20%">
                        <strong>Current location:</strong>
                    </td>
                    <td class="pb-1 pt-1">
                        <span class="text-danger"><strong>{!! getPurchaseCurrentLocation($trails, ($delegations->isEmpty() ? $procurementDelegations : $delegations), $canceled) !!}</strong></span>
                        <span class="badge-pill badge-success float-right">{{ $purchase->requisitionStatus->name}}</span>
                    </td>
                </tr>
            </table>

            <ul class="nav nav-tabs">
                <li class="nav-item"><a data-toggle="tab" class="nav-link active" href="#quotes-card"><strong>Quotations</strong> <span class="badge badge-primary"> {{ count($quotations) }}</span></a></li>
                <li class="nav-item"><a data-toggle="tab" class="nav-link" href="#messages-card"><strong>Q & A</strong> <span class="badge badge-{{ $messages->isEmpty() ?'secondary':'danger' }}"> {{ count($messages) }} </span></a></li>
            </ul>
            <div class="card">
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="quotes-card">
                            <div class="tab-body">
                                <div class="row">
                                    {!! listSupportedDocuments($quotations, 'quote', false) !!}
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="messages-card">
                            <div class="tab-body">
                                {!! listQA($messages) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items -->
        <div class="card mt-3">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <tr>
                        <th>Folio</th>
                        <th>Items.</th>
                        <th>Description</th>
                        <th>UOM</th>
                        <th >Qty Reg</th>
                        <th style="width:90px;">Unit price</th>
                        <th style="width:90px;">currency</th>
                        <th style="width:90px;">Total price</th>
                    </tr>

                    @foreach ($items as $folio => $purchaseItem)
                    <tr>
                        <td>{{ ($folio + 1) }}</td>
                        <td>{{ $purchaseItem->item->name }}</td>
                        <td>{{ $purchaseItem->description }}</td>
                        <td>{{ $purchaseItem->uom }}</td>
                        <td>{{ $purchaseItem->quantity }}</td>
                        <td class="text-right">{{ number_format($purchaseItem->amount, 2) }}</td>
                        <td>{{ $purchaseItem->currency }}</td>
                        <td class="text-right">{{ number_format($purchaseItem->amount * $purchaseItem->quantity, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="bg-light">
                        <td colspan="6"></td>
                        <td class="h3">Total</td>
                        <td class="h3">{{ number_format($total, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- LPO number -->
        <div class="card bg-light mt-3" style="display: none;" id="lpo">
            <div class="card-body">
                {!! Form::open(['method' => 'POST', 'route' => ['purchase/savePoNumber', $purchase->id], 'id' => 'saveOrderNumberForm', 'autocomplete' => 'off']) !!}
                    <div class="row">
                        <div class="col-md-2"><label>LPO Number: </label></div>
                        <div class="col-md-4">
                            {!! Form::text('po_number', null, ['class' => 'form-control', 'required' => 'required', 'autofocus' => true]) !!}
                        </div>
                        <div class="col-md-2">
                            <span data-toggle="tooltip" data-placement="top" title="Save LPO Number and send next user">
                                {!! Form::button('<i class="fa fa-save"></i> Save', ['type' => 'submit', 'class' => 'btn btn-primary', 'id' => 'createOrder']) !!}
                            </span>
                        </div>
                    </div>
                    {!! Form::hidden('purchase_id', $purchase->id) !!}
                {!! Form::close() !!}
            </div>
        </div>

        @if (!empty($purchase->order))
        <div class="card bg-light mt-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 h4"><label>LPO Number: </label></div>
                    <div class="col-md-3 h4">{{$purchase->order->po_number}}</div>
                </div>
            </div>
        </div>
        @endif

        @if (!empty($purchase->supplierEvaluation))
        <div class="card bg-light mt-3">
            <div class="card-header">
                Supplier Rating - <strong>{{ $purchase->supplier->name }}</strong>
            </div>
            <div class="card-body">
                <input id="rated-star" value="{{ $purchase->supplierEvaluation->score }}" class="kv-ltr-theme-fa-star rating-loading" data-size="sm">
                <div>{!! nl2br($purchase->supplierEvaluation->comment) !!}</div>
                <small>( Evaluated on: {{ $purchase->supplierEvaluation->created_at }} by {{ $purchase->supplierEvaluation->createdUser->name }})</small>
            </div>
        </div>
        @endif

        <!-- Buttons -->
        <div class="mt-3 mb-3">
            <!-- Back button -->
            <span data-toggle="tooltip" data-placement="top" title="Back to the requisition list">
                <a href="{{ route('requisition') }}" class="btn btn-outline-dark"><i class="fa fa-backward"></i> Back</a>
            </span>

            <!-- View procurement detail -->
            <span data-toggle="tooltip" data-placement="top" title="View procurement details">
                <a href="{{ route('procurement/show', $purchase->procurement_id) }}" class="btn btn-outline-primary"><i class="fa fa-list"></i> View Requisition</a>
            </span>

            @if ($ableTo['viewVoucher'])
            <!-- View Voucher -->
            <span data-toggle="tooltip" data-placement="top" title="View Voucher details">
                <a href="{{ route('voucher/show', $purchase->voucher->id) }}" class="btn btn-outline-primary"><i class="fa fa-money-bill"></i> View Voucher</a>
            </span>
            @endif

            @if ($ableTo['delegate'])
            <!-- Delegate button -->
            <span data-toggle="tooltip" data-placement="top" title="Reallocate/Transfer">
                <a href="javascript:void(0);" class="btn btn-outline-danger" data-toggle='modal' data-target='#confirm_purchase_delegate'><i class="fa fa-user"></i> Delegate</a>
            </span>
            @endif

            @if ($ableTo['reset'])
            <!-- Reset purchase requisition -->
            <span data-toggle="tooltip" data-placement="top" title="Delete this purchase requisition">
                <a href="javascript:void(0);" class="btn btn-outline-danger" data-toggle='modal' data-target='#confirm_reset_pr'><i class="fa fa-trash"></i> Reset purchase requisition</a>
            </span>
            @endif

            @if ($ableTo['setLpoNumber'])
            <!-- Set LPO Number button -->
            <span data-toggle="tooltip" data-placement="top" title="Set LPO Number" class="float-right ml-2">
                <a href="javascript:void(0);" class='btn btn-lg btn-success' id='set_lpo_number'><i class="fa fa-plus"></i> Set LPO number</a>
            </span>
            @endif

            @if ($ableTo['sendPayment'])
            <!-- Send Payment button -->
            <span data-toggle="tooltip" data-placement="top" title="Approve and send to the accountant for payment" class="float-right ml-2">
                <a href="javascript:void(0);" class="btn btn-lg btn-success" data-toggle="modal" data-target="#sendPaymentModal"><i class="fa fa-money-bill-alt"></i> Send Payment</a>
            </span>
            @endif

            @if ($ableTo['submit'])
            <!-- Submit button -->
            <span data-toggle="tooltip" data-placement="top" title="Approve and send to next office" class="float-right ml-2">
                <a href="javascript:void(0);" class="btn btn-lg btn-primary" data-toggle='modal' data-target='#confirm_purchase_submit'><i class="fa fa-check"></i> Submit</a>
            </span>
            @endif

            @if ($ableTo['return'])
            <!-- Return requisition button -->
            <span data-toggle="tooltip" data-placement="top" title="Return to the previous office" class="float-right">
                <a href="javascript:void(0);" class="btn btn-lg btn-info" data-toggle='modal' data-target='#confirm_purchase_return'><i class="fa fa-reply"></i> Return </a>
            </span>
            @endif

            @if ($ableTo['finishDelegate'])
            <!-- Delegate finish button -->
            <span data-toggle="tooltip" data-placement="top" title="Send back to one who delegated" class="float-right ml-2">
                <a href="javascript:void(0);" class="btn btn-lg btn-primary" data-toggle='modal' data-target='#confirm_finish_delegate' ><i class="fa fa-paper-plane"></i> Delegate back</a>
            </span>
            @endif

            @if ($ableTo['rate'])
            <!-- Rate button -->
            <span data-toggle="tooltip" data-placement="top" title="If you received items, please rate this supplier" class="float-right ml-2">
                <a href="javascript:void(0);" class="btn btn-lg btn-success" data-toggle="modal" data-target="#rateSupplierModal"><i class="fa fa-smile"></i><i class="fa fa-frown"></i> Received item(s) / Rate</a>
            </span>
            @endif
        </div>
    </div>



    <!-- Trails -->
    <div class="col-xl-3 col-lg-4">
        <div class="card bg-light">
            <div class="card-header">
                <h4>Requisition approval trail </h4>
                <h5>[{{ $procurement->unit->name }}]</h5>
            </div>
        </div>

        <table class="table table-bordered table-striped trailTable">
            <tbody>

                <?php
                $i = 1;
                foreach($procurementTrails as $procurementTrail){
                    echo trailTableColumn($i, $procurementTrail, true);
                    $i++;
                }
                ?>
            </tbody>
        </table>

        <table class="table table-bordered table-striped trailTable">
            <thead>
                <th colspan="3">Purchase - {{ $purchase->supplier->name }}</th>
            </thead>
            <tbody>
                <?php
                foreach($trails as $trail){
                    echo trailTableColumn($i, $trail, true);
                    $i++;
                }
                if (!$canceled->isEmpty()) {
                    echo trailTableColumn(($i+1), $canceled[0], true);
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- IDs -->
    <input type="hidden" id="able_type" value="purchases">
    <input type="hidden" id="able_id" value="{{ $purchase->id }}">

</div>

@include('partials.feedback.confirm_modal')

@if ($ableTo['reset'])
@include('purchase.feedback.reset_modal')
@endif
@if ($ableTo['submit'])
@include('purchase.feedback.submit_modal')
@endif
@if ($ableTo['return'])
@include('purchase.feedback.return_modal')
@endif
@if ($ableTo['delegate'])
@include('purchase.feedback.delegate_modal')
@endif
@if ($ableTo['finishDelegate'])
@include('purchase.feedback.finish_delegate_modal')
@endif
@if ($ableTo['rate'])
@include('purchase.feedback.rate_supplier_modal')
@endif
@if ($ableTo['sendPayment'])
@include('purchase.feedback.send_payment_modal')
@endif

@include('partials.feedback.send_message_modal')
@include('partials.feedback.answer_message_modal')
@endsection
