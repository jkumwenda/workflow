@extends('layouts.layout')
@section('title', 'Profile')

@section('content')
<div class="row">
    <div class="col-md-3 ml-auto text-center">
        <i class="fa fa-user-circle fa-10x"></i><br>
        <div class="h3">{{ Auth::user()->name }}</div>
    </div>
    <div class="col-md-8">
        <div class="h1 text-primary">{{ Auth::user()->name }}</div>
        <table class="table-responsive table-striped mt-3">
            <tbody class="h4">
                <tr>
                    <td class="p-2">User ID</td>
                    <td class="p-2">{{ Auth::user()->username }}</td>
                </tr>
                <tr>
                    <td class="p-2">Name</td>
                    <td class="p-2">{{ Auth::user()->name }}</td>
                </tr>
                <tr>
                    <td class="p-2">Salutation</td>
                    <td class="p-2">{{ Auth::user()->salutation }}</td>
                </tr>
                <tr>
                    <td class="p-2">Email Address</td>
                    <td class="p-2">{{ Auth::user()->email }}</td>
                </tr>
                <tr>
                    <td class="p-2">Deaprtment(s) / Unit(s)</td>
                    <td class="p-2">
                        @foreach (Auth::user()->units as $unit)
                            <div>
                                <span>- {{ $unit->name }}</span>
                                <small> - {{ \App\Role::find($unit->pivot->role_id)->description }}</small>
                                @if ($unit->pivot->is_default == "1")
                                <span class="badge badge-danger">Default</span>
                                @endif
                            <div>
                        @endforeach
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td class="p-2">Signature</td>
                    <td class="p-2">
                        <div>
                            @if (empty(Auth::user()->signature))
                            No signature
                            @else
                            <img height="200" src="{!! str_replace(' ', '+', Auth::user()->signature) !!}" />
                            @endif
                        </div>
                        <a href="{{ route('userSetting/signature') }}" class="btn btn-primary btn-sm float-right">Signature Change</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <h5 class="alert alert-warning text-center">Use <a href="http://support.medcol.mw/">ICT Support Helpdesk System</a> to submit all queries regarding your profile</h5>
    </div>
</div>

@endsection