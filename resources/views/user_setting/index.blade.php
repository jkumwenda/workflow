@extends('layouts.layout')
@section('title', 'User Settings')

@section('content')
<div class="col col-md-10 col-md-offset-1">

    <ul class="nav nav-pills flex-column">

        <li class="nav-item">
            <a href="{{ route('userSetting/profile') }}" class="nav-link">
                <h3 class="text text-danger"><i class="fa fa-user-edit"> </i> Profile</h3>
                <small style="color: black;">Check your profile</small>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('userSetting/signature') }}" class="nav-link">
                <h3 class="text text-danger"><i class="fa fa-signature"> </i> Signature</h3>
                <small style="color: black;">Manage application wide electronic signature. The signature will only apply in certain stages of the approval process.</small>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('notifications') }}" class="nav-link">
                <h3 class="text text-danger"><i class="far fa-bell"> </i> Notifications</h3>
                <small style="color:#000000">Setup the application how you would want to manage notification.</small>
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link disabled">
                <h3 class="text text-danger"><i class="fa fa-user-cog"> </i> Delegation</h3>
                <small style="color:#000000">Delegate some of functions to other users.</small>
            </a>
        </li>
    </ul>
</div>
@endsection

