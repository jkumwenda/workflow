@extends('layouts.layout')
@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <h3 class="card-header"><i class="far fa-bell"></i> Notifications</h3>
            <div class="card-body">
                <ul class="list-group">
                    <li class="list-group-item"><a href="{{ route('notifications') }}">Notifications <span class="badge badge-{{ $counts['notifications'] == 0 ? 'primary' : 'danger' }}"> {{ $counts['notifications'] }}</a></li>
                    <li class="list-group-item"><a href="{{ route('requisition/delegation') }} ">Delegated tasks <span class="badge badge-{{ $counts['delegated'] == 0 ? 'primary' : 'danger' }}"> {{ $counts['delegated'] }} </span></a></li>
                </ul>
            </div>
            <div class="card-footer">Click on the notifications</div>
        </div>
    </div>

    <div class="col-md-8">
        <h3><i class="fa fa-chart-bar"></i> My Status</h3>
        <div class="row">
            <!-- To be confirmed -->
            <a class="btn btn-warning text-white col-lg-3 col-md-3 col-sm-5 col-xs-11 m-2" href="{{ route('requisition', ['q' => 'confirmed']) }}">
                <i class="fa fa-pen fa-5x"></i> <br/> <i class="fa-solid fa-pen-clip"></i>
                <h3>To be confirmed</h3>
                <small>Need to confirm</small><br>
                <strong class="h3">{{ $counts['confirmed'] }}</strong>
            </a>
            <!-- In Progress -->
            <a class="btn btn-outline-primary col-lg-3 col-md-3 col-sm-5 col-xs-11 m-2" href="{{ route('requisition', ['q' => 'mine', 'status' => 'inProgress']) }}">
                <i class="fas fa-tasks fa-5x"></i> <br/> 
                <h3>In progress</h3>
                <small>My requisitions</small><br/>
                <strong class="h3">{{ $counts['inProgress'] }}</strong>
            </a>
            <!-- Completed -->
            <a class="btn btn-outline-primary col-lg-3 col-md-3 col-sm-5 col-xs-11 m-2" href="{{ route('requisition', ['q' => 'mine', 'status' => 'Closed']) }}">
                <i class="fa fa-check-square fa-5x"></i> <br/>
                <h3>Completed</h3>
                <small>Completion requisitions</small><br/>
                <strong class="h3">{{ $counts['completed'] }}</strong>
            </a>
        </div>
    </div>

</div>

<div class="row mt-5">
    <div class="col-md-6">
        <div class="card">
            <h3 class="card-header"><i class="fa fa-bookmark"></i> Quick Shortcuts</h3>
            <div class="card-body">
                <div>
                    <a href="#" class="btn btn-warning text-white btn-md" data-toggle='modal' data-target='#createRequisitionModal'><i class="fas fa-plus-circle text-white fa-lg"></i> <br/>Create Requisition</a>
                    <a href="{{ route('requisition', ['q' => 'mine']) }}" class="btn btn-primary btn-md"><i class="fa fa-caret-square-right fa-lg"></i> <br/>My Requisition</a>
                </div>
                <span>For quick access click one of the icon above.</span>
            </div>
        </div>
    </div>
    <div class="col-md-6">
         <div class="card card-primary">
            <h3 class="card-header"><i class="fa fa-lock"></i> For Security</h3>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">Don't reveal a password to ANYONE</li>
                <li class="list-group-item">Don't talk about a password in front of others</li>
                <li class="list-group-item">Don't reveal a password to co-workers while on vacation</li>
            </ul>
        </div>
    </div>
</div>
<div class="row mt-3">
    <div class="col-md-12">
        <h5 class="alert alert-warning text-center">Use <a href="http://support.medcol.mw/">ICT Support Helpdesk System</a> to submit all queries regarding the system and all other IT issues</h5>
    </div>
</div>

@include('requisition.feedback.create_requisition_modal')
@endsection
