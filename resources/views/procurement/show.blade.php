@extends('layouts.layout')
@section('title', 'Procurement Requisition Detail')

@section('scripts')
<script language="javascript">
    $(document).ready(function(){
        // set supplier
        $("#set_supplier").click(function() {
            $("#error-alert").hide();

            //alert for quotations
            if ($("#quotes-card").find(".img-thumbnail").length == 0) {
                $("#error-alert").html("Upload quotations before you create purchase requisitions");
                $("#error-alert").show();
                return false;
            }

            $("#supplier").show("fast", function() {
                $(this).find('.select2').select2();
            });
        });

        //create purchase requisition
        $("#createPurchaseForm").submit(function() {
            $("#error-alert").hide();
            submitCanceled = false;

            var inputted = $("input[name='price[]']").filter(function() {
                return this.value !== '';
            });

            if (inputted.length == 0) {
                $("#error-alert").html("Set at least one price for the items");
                $("#error-alert").show();
                submitCanceled = true;
                return false;
            }
            return true;
        });


        @can('admin')
        // Archive
        $('#archiveModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget) // Button that triggered the modal
            var checked = {{ $procurement->id }};
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
                    <h4>Procurement # - {{ idFormatter('procurements', $procurement->id) }}</h4>
                    <h3>{{ $procurement->title }}</h3>
                </div>
                <div class="card-body">
                    <h4><span class="text-primary">Department/Project:</span> {{ $procurement->unit->name }}</h4>
                    <h5><span class="text-primary">Owner:</span> {{ $procurement->createdUser->name }}</h5>
                    <span class="float-right">( Created on: {{ $procurement->created_at }} )<span>
                </div>
            </div>
            <table class="table table-bordered">
                <tr>
                    <td class="pb-1 pt-1" width="20%">
                        <strong>Current location:</strong>
                    </td>
                    <td class="pb-1 pt-1">
                        <span class="text-danger"><strong>{!! getProcurementCurrentLocation($trails, $purchases, $delegations, $canceled) !!}</strong></span>
                        <span class="badge-pill badge-success float-right">{{ $procurement->requisitionStatus->name}}</span>
                    </td>
                </tr>
            </table>

            <?php
            $activeQuotationTab = ($auth['createPurchase'] || !$quotations->isEmpty());
            ?>
            <ul class="nav nav-tabs">
                <li class="nav-item"><a data-toggle="tab" class="nav-link {{ $activeQuotationTab ? '' : 'active' }}" href="#docs-card"><strong>Supporting documents</strong> <span class="badge badge-{{ $documents->isEmpty()?'secondary':'primary' }}"> {{ count($documents) }}</span></a></li>
                <li class="nav-item"><a data-toggle="tab" class="nav-link {{ $activeQuotationTab ? 'active' : '' }}" href="#quotes-card"><strong>Quotations</strong> <span class="badge badge-{{ $quotations->isEmpty()?'secondary':'primary' }}"> {{ count($quotations) }}</span></a></li>
                <li class="nav-item"><a data-toggle="tab" class="nav-link" href="#messages-card"><strong>Q & A</strong> <span class="badge badge-{{ $messages->isEmpty()?'secondary':'danger' }}"> {{ count($messages) }} </span></a></li>
                <li class="nav-item"><a data-toggle="tab" class="nav-link" href="#changes-card"><strong>Change Log</strong> <span class="badge badge-{{ $changes->isEmpty()?'secondary':'danger' }}"> {{ count($changes) }} </span></a></li>
            </ul>
            <div class="card">
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane {{ $activeQuotationTab ? 'fade' : 'active' }}" id="docs-card">
                            <div class="row">
                                {!! listSupportedDocuments($documents, '', false) !!}
                            </div>
                            <span data-toggle="tooltip" data-placement="top" title="Add supporting documents">
                                <a href="{{ route('procurement/documents', $procurement->id) }}" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i> Add</a>
                            </span>

                        </div>
                        <div class="tab-pane {{ $activeQuotationTab ? 'active' : 'fade' }}" id="quotes-card">
                            <div class="tab-body">
                                <div class="row">
                                    {!! listSupportedDocuments($quotations, 'quote', false) !!}
                                </div>
                                @if ($ableTo['uploadQuotations'])
                                <span  data-toggle="tooltip" data-placement="top" title="Upload quotations">
                                    <a href="{{ route('procurement/quotations', $procurement->id) }}" class="btn btn-sm btn-success"><i class="fa fa-upload"></i> Upload</a>
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="tab-pane fade" id="messages-card">
                            <div class="tab-body">
                                {!! listQA($messages) !!}
                            </div>
                        </div>
                        <div class="tab-pane fade" id="changes-card">
                            <div class="tab-body">
                                @if ($changes->isEmpty())
                                No change logs
                                @else
                                <table class="table-bordered table-sm" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Modified</th>
                                            <th>User</th>
                                            <th>CRUD</th>
                                            <th>Information</th>
                                            <th>ID</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($changes as $change)
                                        <tr>
                                            <td>{{ $change->created_at }}</td>
                                            <td>{{ $change->user->name }}</td>
                                            <td>{{ $change->crud }}</td>
                                            <td>
                                                @if (!empty($change->information['item_id']))
                                                <strong>{{ App\Item::find($change->information['item_id'])->name }}</strong>
                                                @endif
                                                @if (!empty($change->information['description']))
                                                <small>{{ $change->information['description']}}</small>
                                                @endif

                                                @if ($change->crud == 'Update')
                                                <div><strong>[Previous value(s)]</strong></div>
                                                <ul>
                                                    @foreach ($change->changes as $k => $v)
                                                    <li><strong>{{ $k }}</strong>: {{ $v }}</li>
                                                    @endforeach
                                                </ul>
                                                @endif
                                            </td>
                                            <td>{{ !empty($change->information['id']) ? $change->information['id'] : '' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items -->
        {!! Form::open(['method' => 'POST', 'route' => ['procurement/savePrices', $procurement->id], 'id' => 'createPurchaseForm', 'autocomplete' => 'off']) !!}
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
                        <th style="width:90px;">Currency</th>
                        <th style="width:90px;">Total price</th>
                        <th style="width:90px;">Purchase #</th>
                        <th style="width:90px;">Supplier</th>
                        <th style="width:60px;">Status</th>
                        <th></th>
                    </tr>
                    @foreach ($items as $folio => $procurementItem)

                    <?php
                        $previousPurchaseId = $previousPurchaseId ?? null;
                        $emptyPr = empty($procurementItem->purchase_id);
                        $showPrButton = ($previousPurchaseId != $procurementItem->purchase_id);
                    ?>

                    @if ($folio != 0 && $showPrButton)
                    <tr class="bg-light">
                        <td colspan="11"></td>
                    </tr>
                    @endif

                    <tr>
                        {!! Form::hidden('procurement_item_id[]', $procurementItem->id) !!}
                        <td>{{ ($folio + 1) }}</td>
                        <td>{{ $procurementItem->item->name }}</td>
                        <td>{{ $procurementItem->description }}</td>
                        <td>{{ $procurementItem->uom }}</td>
                        <td>{{ $procurementItem->quantity }}</td>
                        @if ($emptyPr && $auth['createPurchase'])
                        <td>{!! Form::number('price[]', null, ['step' => '0.01', 'class' => 'form-control', 'style' => 'width: 90px;']) !!}</td>
                        <td>{!! Form::select('currency[]', $currencies, null, ['class' => 'form-control', 'style' => 'width: 90px;']) !!}</td>
                        @else
                        <td class="text-right">{{ !$emptyPr ? number_format($procurementItem->amount, 2) : '' }}</td>
                        <td class="text-right">{{ !$emptyPr ? $procurementItem->currency : '' }}</td>
                        @endif
                        <td class="text-right">{{ !$emptyPr ? number_format($procurementItem->amount * $procurementItem->quantity, 2) : '' }}</td>
                        <td>
                            @if(!$emptyPr && $showPrButton)
                            <a href="{{ route('purchase/show', $procurementItem->purchase_id) }}">{{ idFormatter('purchases', $procurementItem->purchase->id) }}</a>
                            @endif
                        </td>
                        <td>{{ !$emptyPr && $showPrButton ? $procurementItem->purchase->supplier->name : '' }}</td>
                        <td>{{ !$emptyPr && $showPrButton ? $procurementItem->purchase->requisitionStatus->name : '' }}</td>
                        <td>
                            @if (!$emptyPr && $showPrButton)
                            <span data-toggle="tooltip" data-placement="top" title="See purchase Requisition">
                                <a href="{{ route('purchase/show', $procurementItem->purchase_id) }}" class="btn btn-md btn-warning"><i class="fa fa-list"></i></a>
                            </span>
                            @endif
                        </td>
                    </tr>
                    <?php
                        $previousPurchaseId = $procurementItem->purchase_id;
                    ?>
                    @endforeach
                    <tr class="bg-light">
                        <td colspan="6"></td>
                        <td class="h3">Total</td>
                        <td class="h3" colspan="5">{{ number_format($total, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>


        <!-- Create Purchase Requisition -->
        <div class="card bg-light mt-3" style="display: none" id="supplier">
            <div class="card-header">
                <span class="h3">Set supplier</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-1 col-md-3">
                        <label>Supplier: </label>
                    </div>
                    <div class="col-lg-4 col-md-8">
                        {!! Form::select('supplier', $suppliers, null, ['class' => 'form-control select2','required' => 'required', 'placeholder' => ' - Select - ']) !!}
                    </div>
                    <div class="col-lg-1 col-md-3">
                        <label>Route: </label>
                    </div>
                    <div class="col-lg-2 col-md-5">
                        {!! Form::select('route', ['Cheque' => 'Cheque', 'LPO' => 'LPO'], null, ['class' => 'form-control','required' => 'required', 'placeholder' => ' - Select - ']) !!}
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <span data-toggle="tooltip" data-placement="top" title="Create purchase requisition">
                            {!! Form::button('<i class="fa fa-save"></i> Save', ['type' => 'submit', 'class' => 'btn btn-primary', 'id' => 'createPurchase']) !!}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {!! Form::hidden('procurement_id', $procurement->id) !!}
        {!! Form::close() !!}

        <!-- Buttons -->
        <div class="mt-3 mb-3">
            <!-- // --- Left side ------------------------------------------------- -->
            <!-- Back button for normal -->
            <span data-toggle="tooltip" data-placement="top" title="Back to the requisition list" class="">
                <a href="{{ route('requisition') }}" class="btn btn-outline-dark"><i class="fa fa-backward"></i> Back</a>
            </span>

            @if ($ableTo['delegate'])
            <!-- Delegate task Button -->
            <span data-toggle="tooltip" data-placement="top" title="Assign this requisition to another staff" class="ml-2">
                <a href="javascript:void(0);" class="btn btn-outline-danger" data-toggle='modal' data-target='#confirm_procurement_delegate'><i class="fa fa-user"></i> Delegate task</a>
            </span>
            @endif

            @if ($ableTo['amend'])
            <!-- Amend button -->
            <span data-toggle="tooltip" data-placement="top" title="Modify this requisition" class="ml-2">
                <a href="{{ route('procurement/amend', $procurement->id) }}" class='btn btn-outline-primary' id='amend'> <i class='fa fa-edit'></i> Amend</a>
            </span>
            @endif

            @if ($ableTo['delete'])
            <!-- Delete button -->
            <span data-toggle="tooltip" data-placement="top" title="Delete this requisition" class="ml-2">
                <a href="javascript:void(0);" class='btn btn-outline-danger' data-toggle='modal' data-target='#confirm_delete'> <i class='fa fa-trash'></i> Delete </a>
            </span>
            @endif

            @if ($ableTo['cancel'])
            <!-- Cancel button -->
            <span data-toggle="tooltip" data-placement="top" title="Cancel/terminate this requisition" class="ml-2">
                <a href="javascript:void(0);" class='btn btn-outline-danger' data-toggle='modal' data-target='#confirm_cancel' > <i class='fa fa-ban'></i> Cancel </a>
            </span>
            @endif

            @if ($ableTo['changeOwner'])
            <!-- Change Owner button -->
            <span data-toggle="tooltip" data-placement="top" title="Changing requisition owner" class="ml-2">
                <a href="javascript:void(0);" class='btn btn-outline-primary' data-toggle='modal' data-target='#change_owner_modal' > <i class='fa fa-retweet'></i> Change Owner </a>
            </span>
            @endif

            @if ($ableTo['archive'])
            <!-- Archive -->
            <span data-toggle="tooltip" data-placement="top" title="Administrator only" class="ml-2">
                <a href="javascript:void(0);" class='btn btn-outline-danger' data-toggle='modal' data-target='#archiveModal' data-archive='true' > <i class='fa fa-file-archive'></i> Archive </a>
            </span>
            @endif

            @if ($ableTo['unarchive'])
            <!-- Unarchive -->
            <span data-toggle="tooltip" data-placement="top" title="Administrator only" class="ml-2">
                <a href="javascript:void(0);" class='btn btn-outline-danger' data-toggle='modal' data-target='#archiveModal' daa-archive='false' > <i class='fa fa-file-archive'></i> Unarchive </a>
            </span>
            @endif


            <!-- // --- Right side ------------------------------------------------- -->
            @if ($ableTo['createPurchase'])
            <!-- Set Supplier button -->
            <span data-toggle="tooltip" data-placement="top" title="Select supplier and route" class="float-right ml-2">
                <a href="javascript:void(0);" class='btn btn-lg btn-primary' id='set_supplier'><i class="fa fa-truck"></i> Create purchase requisition</a>
            </span>
            @endif

            @if ($ableTo['submit'])
            <!-- Submit button -->
            <span data-toggle="tooltip" data-placement="top" title="Approve and send to the next office" class="float-right ml-2">
                <a href="javascript:void(0);" class='btn btn-lg btn-primary' data-toggle='modal' data-target='#confirm_submit'> <i class='fa fa-check'></i> Submit </a>
            </span>
            @endif

            @if ($ableTo['return'])
            <!-- Return button -->
            <span data-toggle="tooltip" data-placement="top" title="Return to the previous office" class="float-right ml-2">
                <a href="javascript:void(0);" class='btn btn-lg btn-info' data-toggle='modal' data-target='#confirm_return' > <i class='fa fa-reply'></i> Return </a>
            </span>
            @endif

            @if ($ableTo['finishDelegate'])
            <!-- Delegate finish button -->
            <span data-toggle="tooltip" data-placement="top" title="Send back to one who delegated" class="float-right ml-2">
                <a href="javascript:void(0);" class="btn btn-lg btn-primary" data-toggle='modal' data-target='#confirm_finish_delegate' ><i class="fa fa-paper-plane"></i> Delegate back</a>
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
                    echo trailTableColumn($i, $trail, true);
                    $i++;
                }
                if (!$canceled->isEmpty()) {
                    echo trailTableColumn(($i+1), $canceled[0], true);
                }
                ?>
            </tbody>
        </table>

        @foreach($purchases as $purchase)
        <table class="table table-bordered table-striped trailTable">
            <thead>
                <tr>
                    <th colspan="3">{{ $purchase->supplier->name }} - <a href="{{ route('purchase/show', $purchase->id) }}">{{ idFormatter('purchase', $purchase->id) }}</a></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $purchaseTrails = $purchase->trails()->get();
                $i = 1;
                foreach($purchaseTrails as $trail){
                    echo trailTableColumn($i, $trail, true);
                    $i++;
                }
                if (!$canceled->isEmpty()) {
                    echo trailTableColumn(($i+1), $canceled[0], true);
                }
                ?>
            </tbody>
        </table>
        @endforeach

    </div>

    <!-- IDs -->
    <input type="hidden" id="able_type" value="procurements">
    <input type="hidden" id="able_id" value="{{ $procurement->id }}">
</div>


@if ($ableTo['submit'])
@include('procurement.feedback.submit_modal')
@endif
@if ($ableTo['return'])
@include('procurement.feedback.return_modal')
@endif
@if ($ableTo['delegate'])
@include('procurement.feedback.delegate_modal')
@endif
@if ($ableTo['delete'])
@include('procurement.feedback.delete_modal')
@endif
@if ($ableTo['cancel'])
@include('procurement.feedback.cancel_modal')
@endif
@if ($ableTo['finishDelegate'])
@include('procurement.feedback.finish_delegate_modal')
@endif
@if ($ableTo['changeOwner'])
@include('procurement.feedback.change_owner_modal')
@endif
@if ($ableTo['archive'] || $ableTo['unarchive'])
@include('procurement.feedback.archive_modal')
@endif

@include('partials.feedback.send_message_modal')
@include('partials.feedback.answer_message_modal')

@endsection