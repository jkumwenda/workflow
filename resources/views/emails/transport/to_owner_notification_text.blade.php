Dear {{ $transport->travel->createdUser->name }}

@if (in_array($submitType, ['submit', 'return']))
Your travel requisition, {{ $transport->travel->procurement->title }}, has been {{ $submitType == 'submit' ? 'submitted' : 'returned' }} to {{ $nextUser->name }} by {{ $nextFlow->role->description }}.
@elseif ($submitType == 'delegate')
Your travel requisition, {{ $transport->travel->procurement->title }}, has been delegated to {{ $nextUser->name }} for further processing.
@elseif ($submitType == 'cancel')
Your travel requisition, {{ $transport->travel->procurement->title }}, has been canceled by {{ $nextUser->name }}.
@elseif ($submitType == 'approve')
Your travel requisition's transport, {{ $transport->travel->procurement->title }}, has been approved by {{ $nextUser->name }}.
@elseif ($submitType == 'changeOwner')
You have become a new owner of the travel requisition, {{ $transport->travel->procurement->title }}. For more detail, see below;
@endif

---------
Travel Requisition ID: {{ idFormatter('travel', $transport->travel->id) }}
Title: {{ $transport->travel->procurement->title }}
---------

{{ route('travel/show', $transport->travel->id) }}


@if (!empty($currentTrail) && !empty($currentTrail->comment))
{{ $currentTrail->user->name }}(Previous office)'s comment
{{ $currentTrail->comment }}
@endif