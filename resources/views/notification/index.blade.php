@extends('layouts.layout')
@section('title', 'Notifications')

@section('top-content')
<div class="text-right">
</div>
@endsection
@section('content')

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($notifications as $notification)
            <tr>
                <td class="row">
                    <div class="col-md-10">
                        <a href="{{ $notification->url }}">
                            <div class="h5"><i class="fa rplus-icon-{{ $notification->type }}"></i> {{ $notification->type }} - # {{ $notification->formattedId }}</div>
                            <div class="text-dark">{{ $notification->data['notification'] }}</div>
                            @if (!empty($notification->comment))
                            <div class="trail-comment">{!! nl2br($notification->comment) !!}</div>
                            @endif
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('notifications', ['read' => $notification->id]) }}" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i> Mark as read</a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>


{{ $notifications->appends(request()->input())->links() }}

@endsection