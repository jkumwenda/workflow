@extends('layouts.layout')
@section('title', 'Subsistence Requisition Details')

@section('top-content')
@if($requisition->archived)
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
                    <h4>Travel # - {{ idFormatter('travels', $travel->id) }}</h4>
                    <h3>{{$requisition->title}}</h3>
                    <i>{{$travel->purpose}}</i>
                </div>
                <div class="card-body">
                    <h4><span class="text-primary">Department/Project:</span>{{$requisition->unit->name}}</h4>
                    <h5><span class="text-primary">Owner:</span> {{$requisition->createdUser->name}}</h5>
                    <span class="float-right">( Created on: {{ $requisition->created_at }} )<span>
                </div>
            </div>
            <table class="table table-bordered">
                <tr>
                    <td class="pb-1 pt-1" width="20%">
                        <strong>Current location:</strong>
                    </td>
                    <td class="pb-1 pt-1">
                        <span class="text-danger"><strong>{!! getSubsistenceCurrentLocation($subsistenceTrails, $delegations, $canceled) !!}</strong></span>
                        <span
                            class="badge-pill badge-success float-right text-white">{{$subsistence->requisitionStatus->name}}</span>
                    </td>
                </tr>
            </table>

            <?php
            $activeQuotationTab = ($auth['travel'] || !$quotations->isEmpty());
            ?>
            <ul class="nav nav-tabs">
                <li class="nav-item"><a data-toggle="tab" class="nav-link {{ $activeQuotationTab?'':'active' }}"
                        href="#details-card"><strong>Travel details</strong> </a></li>
                <li class="nav-item"><a data-toggle="tab" class="nav-link {{-- $activeQuotationTab?'active':'' --}}"
                        href="#vehicle-card"><strong>Vehicle details</strong> </a></li>
                <li class="nav-item"><a data-toggle="tab" class="nav-link {{-- $activeQuotationTab?'':'active' --}}"
                        href="#docs-card"><strong>Supporting documents</strong> <span class="badge badge-{{ $documents->isEmpty()?'secondary':'primary' }}">
                            {{ count($documents) }}</span></a></li>
            </ul>
            <div class="card">
                <div class="card-body bg-light">
                    <div class="tab-content">
                        <div class="tab-pane active" id="details-card">
                            <div class="row">
                                <table class="table ">
                                    <tr>
                                        <th>Origin</th>
                                        <th>Destination</th>
                                        <th>Departure Date</th>
                                        <th>Return Date</th>
                                        <th>Days</th>
                                        <th>Number of travellers</th>
                                        <th>Preferred vehicle type</th>
                                    </tr>

                                    <tr>
                                        <td>{{$travel->campus->name}}</td>
                                        <td>{{ $travel->district->name }}</td>
                                        <td>{{ dateformat($travel->datetime_out)}}</td>
                                        <td>{{ dateformat($travel->datetime_in) }}</td>
                                        <td>{{$days}}</td>
                                        <th>{{number_format($numberOfTravellers,0)}}</th>
                                        <th>{{$travel->vehicleType->name}}</th>
                                    </tr>
                                </table>
                            </div>

                        </div>
                        <div class="tab-pane fade" id="vehicle-card">
                            <div class="tab-body">
                                <div class="row">
                                    <table class="table">
                                        <tr>
                                            <th>Registration</th>
                                            <th>Type</th>
                                            <th>Make</th>
                                            <th>Model</th>
                                            <th>Department</th>
                                            <th>Capacity</th>
                                            <th>Driver</th>
                                        </tr>


                                        <tr>
                                            <td>{{ $travel->vehicle->registration_number?? 'Pending' }}</td>
                                            <td>{{ $travel->vehicle->vehicleType->name ?? 'Pending' }}</td>
                                            <td>{{ $travel->vehicle->make->make ?? 'Pending' }}</td>
                                            <td>{{ $travel->vehicle->name ?? 'Pending' }}</td>
                                            <td>{{ $travel->vehicle->unit->name ?? 'Pending' }}</td>
                                            <td>{{ $travel->vehicle->capacity ?? 'Pending' }}</td>
                                            <td>{{ $travel->driver->name ?? 'Pending' }}</td>
                                        </tr>
                                    </table>
                                </div>

                            </div>
                        </div>
                        <div class="tab-pane fade {{-- $activeQuotationTab?'fade':'active' --}}" id="docs-card">
                            <div class="row">
                                {!! listSupportedDocuments($documents, '', false) !!}
                            </div>
                            <span data-toggle="tooltip" data-placement="top" title="Add supporting documents">
                                <a href="{{ route('travel/documents', $travel->id) }}"
                                    class="btn btn-sm btn-warning text-white"><i class="fa fa-plus"></i> Add</a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="card mt-3 b-0 border">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <tr>
                        <th class="text-center h4" colspan="8">List of travellers</th>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Phone number</th>
                        <th>Departure date</th>
                        <th>Return date</th>
                        <th>Days</th>
                        <th>Amount/Day</th>
                        <th>Total</th>
                    </tr>
                    @foreach ($subsistence->travellers as $traveller)
                    <tr>
                        <td>{{ $traveller->user->first_name }} {{ $traveller->user->surname }}</td>
                        <td>{{ \App\User::find($traveller->user_id)->units()->first()->name }}</td>
                        <td>{{ $traveller->user->phone_number?? 'Not available' }}</td>
                        <td>{{ dateformat($traveller->departure_date) }}</td>
                        <td>{{ dateformat($traveller->return_date) }}</td>
                        <td>{{$travellerDays[$loop->iteration-1]}}</td>
                        <td>MK {{number_format($traveller->amount,2)  }}</td>
                        <td>MK {{number_format($amount[$loop->iteration-1], 2)}}</td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>

        <!-- Buttons -->
        <div class="mt-3 mb-3">
            <!-- // --- Left side ------------------------------------------------- -->
            <!-- Back button for normal -->
            <span data-toggle="tooltip" data-placement="top" title="Back to the requisition list" class="">
                <a href="{{ route('requisition') }}" class="btn btn-outline-warning"><i class="fa fa-backward"></i>
                    Back</a>
            </span>

            @if ($ableTo['delegate'])
            <!-- Delegate task Button -->
            <span data-toggle="tooltip" data-placement="top" title="Assign this requisition to another staff"
                class="ml-2">
                <a href="javascript:void(0);" class="btn btn-outline-danger" data-toggle='modal'
                    data-target='#confirm_procurement_delegate'><i class="fa fa-user"></i> Delegate task</a>
            </span>
            @endif

            @if ($ableTo['amend'])
            <!-- Amend button -->
            <span data-toggle="tooltip" data-placement="top" title="Modify this requisition" class="ml-2">
                <a href="{{ route('travel/amend', $travel->id) }}" class='btn btn-outline-warning' id='amend'>
                    <i class='fa fa-edit'></i> Amend</a>
            </span>
            @endif

            @if ($ableTo['delete'])
            <!-- Delete button -->
            <span data-toggle="tooltip" data-placement="top" title="Delete this requisition" class="ml-2">
                <a href="javascript:void(0);" class='btn btn-outline-danger' data-toggle='modal'
                    data-target='#confirm_delete'> <i class='fa fa-trash'></i> Delete </a>
            </span>
            @endif

            @if ($ableTo['cancel'])
            <!-- Cancel button -->
            <span data-toggle="tooltip" data-placement="top" title="Cancel/terminate this requisition" class="ml-2">
                <a href="javascript:void(0);" class='btn btn-outline-danger' data-toggle='modal'
                    data-target='#confirm_cancel'> <i class='fa fa-ban'></i> Cancel </a>
            </span>
            @endif

            @if ($ableTo['changeOwner'])
            <!-- Change Owner button -->
            <span data-toggle="tooltip" data-placement="top" title="Changing requisition owner" class="ml-2">
                <a href="javascript:void(0);" class='btn btn-outline-warning' data-toggle='modal'
                    data-target='#change_owner_modal'> <i class='fa fa-retweet'></i> Change Owner </a>
            </span>
            @endif

            @if ($ableTo['archive'])
            <!-- Archive -->
            <span data-toggle="tooltip" data-placement="top" title="Administrator only" class="ml-2">
                <a href="javascript:void(0);" class='btn btn-outline-danger' data-toggle='modal'
                    data-target='#archiveModal' data-archive='true'> <i class='fa fa-file-archive'></i> Archive </a>
            </span>
            @endif

            @if ($ableTo['unarchive'])
            <!-- Unarchive -->
            <span data-toggle="tooltip" data-placement="top" title="Administrator only" class="ml-2">
                <a href="javascript:void(0);" class='btn btn-outline-danger' data-toggle='modal'
                    data-target='#archiveModal' daa-archive='false'> <i class='fa fa-file-archive'></i> Unarchive </a>
            </span>
            @endif


            <!-- // --- Right side ------------------------------------------------- -->

            @if ($ableTo['submit'])
            <!-- Submit button -->
            <span data-toggle="tooltip" data-placement="top" title="Approve and send to the next office"
                class="float-right ml-2">
                <a href="javascript:void(0);" class='btn btn-lg btn-warning text-white' data-toggle='modal'
                    data-target='#confirm_submit'> <i class='fa fa-check'></i> Submit </a>
            </span>
            @endif
            @if ($ableTo['subsistence'])
            <!-- Delegate finish button -->
            <span data-toggle="tooltip" data-placement="top" title="Send back to one who delegated"
                class="float-right ml-2">
                <a href="javascript:void(0);" class="btn btn-lg btn-warning text-white" data-toggle='modal'
                    data-target='#approve_subsistence'><i class='fa fa-check'></i> Approve </a>
            </span>
            @endif

            @if ($ableTo['return'])
            <!-- Return button -->
            <span data-toggle="tooltip" data-placement="top" title="Return to the previous office"
                class="float-right ml-2">
                <a href="javascript:void(0);" class='btn btn-lg btn-info' data-toggle='modal'
                    data-target='#confirm_return'> <i class='fa fa-reply'></i> Return </a>
            </span>
            @endif



            @if ($ableTo['finishDelegate'])
            <!-- Delegate finish button -->
            <span data-toggle="tooltip" data-placement="top" title="Send back to one who delegated"
                class="float-right ml-2">
                <a href="javascript:void(0);" class="btn btn-lg btn-warning" data-toggle='modal'
                    data-target='#confirm_finish_delegate'><i class="fa fa-paper-plane"></i> Delegate back</a>
            </span>
            @endif

        </div>
    </div>

    <!-- Trails -->
    <div class="col-xl-3 col-lg-4">
        <div class="card bg-light">
            <div class="card-header">
                <h4>Requisition approval trail </h4>
                <h5>[{{ $requisition->unit->name }}]</h5>
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

        @if ($transport)
        <table class="table table-bordered table-striped trailTable">
            <thead>
                <tr>
                    <th colspan="3">Transport - <a href="{{ route('transport/show', $transport->id) }}">{{
                            idFormatter('transport', $transport->id) }}</a></th>
                </tr>
            </thead>
            <tbody>
                @php
            $i = 1;
            foreach($transportTrails as $trail){
                echo trailTableColumn($i, $trail, true);
                $i++;
            }
            if (!$canceled->isEmpty()) {
                echo trailTableColumn(($i+1), $canceled[0], true);
            }
            @endphp
            </tbody>
        </table>
        @endif

        @if ($subsistence)
        <table class="table table-bordered table-striped trailTable">
            <thead>
                <tr>
                    <th colspan="3">Subsistence - <a href="{{ route('subsistence/show', $subsistence->id) }}">{{
                            idFormatter('subsistence', $subsistence->id) }}</a></th>
                </tr>
            </thead>
            <tbody>
                @php

                $i = 1;
                foreach($subsistenceTrails as $trail){
                    echo trailTableColumn($i, $trail, true);
                    $i++;
                }
                if (!$canceled->isEmpty()) {
                    echo trailTableColumn(($i+1), $canceled[0], true);
                }
                @endphp

            </tbody>
        </table>
            <a href="{{ route('downloadSubsistence-pdf', $subsistence->id) }}" class="text-white btn btn-warning"> Download </a>
        @endif
    </div>

    <!-- IDs -->
    <input type="hidden" id="able_type" value="procurements">
    <input type="hidden" id="able_id" value="{{ $requisition->id }}">
</div>




@if ($ableTo['submit'])
@include('subsistence.feedback.submit_modal')
@endif
@if ($ableTo['subsistence'])
@include('subsistence.feedback.subsistence_modal')
@endif
@if ($ableTo['return'])
@include('subsistence.feedback.return_modal')
@endif
@if ($ableTo['delegate'])
@include('subsistence.feedback.delegate_modal')
@endif
@if ($ableTo['delete'])
@include('subsistence.feedback.delete_modal')
@endif
@if ($ableTo['cancel'])
@include('subsistence.feedback.cancel_modal')
@endif
@if ($ableTo['finishDelegate'])
@include('subsistence.feedback.finish_delegate_modal')
@endif
@if ($ableTo['changeOwner'])
@include('subsistence.feedback.change_owner_modal')
@endif
@if ($ableTo['archive'] || $ableTo['unarchive'])
@include('subsistence.feedback.archive_modal')
@endif

@include('partials.feedback.send_message_modal')
@include('partials.feedback.answer_message_modal')

@endsection
