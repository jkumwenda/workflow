Dear {{ $travel->createdUser->name }}

@if (in_array($submitType, ['submit', 'return']))
Your travel requisition, {{ $travel->procurement->title }}, has been {{ $submitType == 'submit' ? 'submitted' : 'returned' }} to {{ $nextUser->name }} by {{ $nextFlow->role->description }}.
@elseif ($submitType == 'delegate')
Your travel requisition, {{ $travel->procurement->title }}, has been delegated to {{ $nextUser->name }} for further processing.
@elseif ($submitType == 'cancel')
Your travel requisition, {{ $travel->procurement->title }}, has been canceled by {{ $nextUser->name }}.
@elseif ($submitType == 'approve')
Your travel requisition, {{ $travel->procurement->title }}, has been approved by {{ $nextUser->name }}.
@elseif ($submitType == 'changeOwner')
You have become a new owner of the travel requisition, {{ $travel->procurement->title }}. For more detail, see below;
@endif

---------
Travel Requisition ID: {{ idFormatter('travel', $travel->id) }}
Title: {{ $travel->procurement->title }}
---------

{{ route('travel/show', $travel->id) }}


@if (!empty($currentTrail) && !empty($currentTrail->comment))
{{ $currentTrail->user->name }}(Previous office)'s comment
{{ $currentTrail->comment }}
@endif