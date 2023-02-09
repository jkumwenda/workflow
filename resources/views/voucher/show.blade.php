@extends('layouts.layout')
@section('title', 'Voucher Requisition Detail')

@section('scripts')
<script language="javascript">
    $(document).ready(function(){
        if ($('#set_bank_tax_modal').length) {
            var calcAmount = function() {
                var total_amount = $('#total_amount').data('total_amount') * 1;
                var excepted_tax = $('input[name=excepted_tax]').val() * 1;
                var tax_applied = $('input[name=tax_applied').val() * 1;

                var taxable = total_amount - excepted_tax;
                var taxed_amount = taxable * (tax_applied / 100);
                var expenditure = (taxable - taxed_amount) + excepted_tax;

                var toLocalStringOption = {minimumFractionDigits: 2, maximumFractionDigits: 2};
                $('.excepted_tax').text(excepted_tax.toLocaleString('us', toLocalStringOption));
                $('.tax_applied').text(tax_applied.toLocaleString('us', toLocalStringOption));
                $('.taxable').text(taxable.toLocaleString('us', toLocalStringOption));
                $('.taxed_amount').text(taxed_amount.toLocaleString('us', toLocalStringOption));
                $('.expenditure').text(expenditure.toLocaleString('us', toLocalStringOption));
            };

            $('input[name=excepted_tax],input[name=tax_applied]').change(function(e) {
                calcAmount();
            });
            $('#calc_amount', function() {
                calcAmount();
            });
        }
    });
</script>
@endsection

@section('top-content')
@endsection

@section('content')
<div class="row">
    <div class="col-xl-9 col-lg-8 " style="padding-bottom: 15px;">


        <!-- Header -->
        <div>
            <div class="card bg-light">
                <div class="card-header">
                    <h4>Voucher # - {{ idFormatter('voucher', $voucher->id) }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <span class="text-primary h5">Department/Project:</span></br>
                                    <span class="h4">{{ $procurement->unit->name }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <span class="text-primary h5">Supplier (Pay to):</span></br>
                                    <span class="h4">{{ $purchase->supplier->name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <span class="float-right">( Voucher created on: {{ $voucher->created_at }} )</span>
                </div>
            </div>
            <table class="table table-bordered">
                <tr>
                    <td class="pb-1 pt-1" width="20%">
                        <strong>Current location:</strong>
                    </td>
                    <td class="pb-1 pt-1">
                        <span class="text-danger"><strong>{!! getVoucherCurrentLocation($trails) !!}</strong></span>
                        <span class="badge-pill badge-success float-right">{{ $voucher->requisitionStatus->name}}</span>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Requisition detail -->
        <div class="card card-info mt-3">
            <div class="card-header">Requisition detail</div>
            <div class="card-body">
                <h5><span class="text-primary">Procurement #:  </span>{{ idFormatter('procurement', $procurement->id) }}</h5>
                <h4><span class="text-primary">Title: </span>{{ $procurement->title }}</h4>

                <table class="table table-bordered table-hover table-info mt-4">
                    <tr>
                        <td>REQUISITION AMOUNT:</td>
                        <td class="text-right h4">{{ moneyFormatter($voucher->total_amount) }}</td>
                        <td>EXEMPTED AMOUNT(VAT,LEVY):</td>
                        <td class="text-right h4">{{ moneyFormatter($voucher->excepted_tax) }}</td>
                    </tr>
                </table>

                <a data-toggle="collapse" href="#requisitionItems" role="button" aria-expanded="false" aria-controls="requisitionItems">See items</a>

                <!-- Items -->
                <div class="card mt-3 collapse" id="requisitionItems">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-hover">
                            <tr>
                                <th>Folio</th>
                                <th>Items.</th>
                                <th>Description</th>
                                <th>UOM</th>
                                <th >Qty Reg</th>
                                <th style="width:90px;">Unit price</th>
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
                                <td class="text-right">{{ number_format($purchaseItem->amount * $purchaseItem->quantity, 2) }}</td>
                            </tr>
                            @endforeach
                            <tr class="bg-light">
                                <td colspan="5"></td>
                                <td class="h4">Total</td>
                                <td class="h4">{{ moneyFormatter($voucher->total_amount) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>


        <div class="card mt-3">
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <tr>
                        <th></th>
                        <th>Code</th>
                        <th style="width:170px"></th>
                        <th style="width:90px">Amount</th>
                    </tr>
                    <tr>
                        <td>EXPENDITURE</td>
                        <td>{{ $voucher->expenditure_code }}</td>
                        <td class="text-right"></td>
                        <td class="text-right">{{ moneyFormatter($voucher->excepted_tax + ($voucher->taxable - $voucher->taxedAmount)) }}</td>
                    </tr>
                    <tr>
                        <td>WITHHOLDING TAX</td>
                        <td>{{ $voucher->withholding_tax_code }}</td>
                        <td class="text-right">{{ $voucher->tax_applied}} %</td>
                        <td class="text-right">{{ moneyFormatter($voucher->taxedAmount) }}</td>
                    </tr>
                </table>
                <div><strong>TAXABLE:</strong> {{ moneyFormatter($voucher->taxable) }} (Requisition Amount {{ moneyFormatter($voucher->total_amount) }} - Excepted tax {{ moneyFormatter($voucher->excepted_tax) }})</div>
                <div><strong>TAXED AMOUNT:</strong> {{ moneyFormatter($voucher->taxedAmount)}} (Taxable {{ moneyFormatter($voucher->taxable) }} * {{ $voucher->tax_applied ?? 0 }}% )</div>
                @if ($ableTo['setBank'])
                <!-- set bank and tax -->
                <span class="float-right" data-toggle="tooltip" data-placement="top" title="Set the expenditure and the withholding tax">
                    <a href="javascript:void(0);" class="btn btn-success" data-toggle='modal' data-target='#set_bank_tax_modal'><i class="fa fa-plus"></i> Set expenditure and withholding tax</a>
                </span>
                @endif
            </div>
        </div>


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

            <!-- View purchase detail -->
            <span data-toggle="tooltip" data-placement="top" title="View purchase details">
                <a href="{{ route('purchase/show', $purchase->id) }}" class="btn btn-outline-primary"><i class="fa fa-shopping-cart"></i> View Purchase</a>
            </span>

            @if ($ableTo['transfer'])
            <!-- Transfer button -->
            <span data-toggle="tooltip" data-placement="top" title="Reallocate/Transfer">
                <a href="javascript:void(0);" class="btn btn-outline-danger" data-toggle='modal' data-target='#confirm_voucher_transfer'><i class="fa fa-user"></i> Transfer</a>
            </span>
            @endif

            @if ($ableTo['paid'])
            <!-- Paid (close requisition) -->
            <span data-toggle="tooltip" data-placement="top" title="Paid by cheque and close requisition" class="float-right ml-2">
                <a href="javascript:void(0);" class="btn btn-lg btn-success" data-toggle='modal' data-target='#confirmModal' data-item="{{ route('voucher/paid', $voucher->id) }}"><i class="fa fa-cash-register"></i> Paid (Close requisition) </a>
            </span>
            @endif

            @if ($ableTo['submit'])
            <!-- Submit button -->
            <span data-toggle="tooltip" data-placement="top" title="Approve and send to next office" class="float-right ml-2">
                <a href="javascript:void(0);" class="btn btn-lg btn-primary" data-toggle='modal' data-target='#confirm_voucher_submit'><i class="fa fa-paper-plane"></i> Submit(Send Next)</a>
            </span>
            @endif

            @if ($ableTo['return'])
            <!-- Return requisition button -->
            <span data-toggle="tooltip" data-placement="top" title="Return to the previous office" class="float-right">
                <a href="javascript:void(0);" class="btn btn-lg btn-info" data-toggle='modal' data-target='#confirm_voucher_return'><i class="fa fa-reply"></i> Return(Send Back) </a>
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
                foreach($trails as $trail){
                    echo trailTableColumn($i, $trail);
                    $i++;
                }
                if (!$canceled->isEmpty()) {
                    echo trailTableColumn(($i+1), $canceled[0]);
                }
                ?>
            </tbody>
        </table>
    </div>


</div>

@include('partials.feedback.confirm_modal')

@if ($ableTo['submit'])
@include('voucher.feedback.submit_modal')
@endif
@if ($ableTo['return'])
@include('voucher.feedback.return_modal')
@endif
@if ($ableTo['transfer'])
@include('voucher.feedback.transfer_modal')
@endif
@if ($ableTo['setBank'])
@include('voucher.feedback.set_bank_tax_modal')
@endif
@endsection
