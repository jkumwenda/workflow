Dear {{ $procurement->createdUser->name }}

@if (in_array($submitType, ['submit', 'return']))
Your procurement requisition, {{ $procurement->title }}, has been {{ $submitType == 'submit' ? 'submitted' : 'returned' }} to {{ $nextUser->name }} by {{ $nextFlow->role->description }}.
@elseif ($submitType == 'delegate')
Your procurement requisition, {{ $procurement->title }}, has been delegated to {{ $nextUser->name }} for further processing.
@elseif ($submitType == 'cancel')
Your procurement requisition, {{ $procurement->title }}, has been canceled by {{ $nextUser->name }}.
@elseif ($submitType == 'changeOwner')
You have become a new owner of the procurement requisition, {{ $procurement->title }}. For more detail, see below;
@endif

---------
Procurement Requisition ID: {{ idFormatter('procurement', $procurement->id) }}
Title: {{ $procurement->title }}
---------

{{ route('procurement/show', $procurement->id) }}


@if (!empty($currentTrail) && !empty($currentTrail->comment))
{{ $currentTrail->user->name }}(Previous office)'s comment
{{ $currentTrail->comment }}
@endif

@if (!empty($comment))
{!! nl2br($comment) !!}
@endif


Thank you for using R-Plus


Auto generated mail