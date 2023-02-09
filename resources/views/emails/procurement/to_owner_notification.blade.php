Dear {{ $procurement->createdUser->name }}<br/>
<br/>
@if (in_array($submitType, ['submit', 'return']))
Your procurement requisition, {{ $procurement->title }}, has been {{ $submitType == 'submit' ? 'submitted' : 'returned' }} to {{ $nextUser->name }} by {{ $nextFlow->role->description }}. <br/>
@elseif ($submitType == 'delegate')
Your procurement requisition, {{ $procurement->title }}, has been delegated to {{ $nextUser->name }} for further processing. <br/>
@elseif ($submitType == 'cancel')
Your procurement requisition, {{ $procurement->title }}, has been canceled by {{ $nextUser->name }}. <br/>
@elseif ($submitType == 'changeOwner')
You have become a new owner of the procurement requisition, {{ $procurement->title }}. For more detail, see below; <br/>
@endif
<br/>
<a href="{{ route('procurement/show', $procurement->id) }}">{{ route('procurement/show', $procurement->id) }}</a><br/>
<br/>
<hr>
Procurement Requisition ID: {{ idFormatter('procurement', $procurement->id) }}<br/>
Title: {{ $procurement->title }}<br/>
<hr>
<br/>
@if (!empty($currentTrail) && !empty($currentTrail->comment))
<strong>{{ $currentTrail->user->name }}(Previous office)'s comment</strong><br/>
{!! nl2br($currentTrail->comment) !!}
@endif

@if (!empty($comment))
{!! nl2br($comment) !!}
@endif

<br/>
<b>Thank you for using R-Plus</b><br/>
<br/>
<br/>
Auto generated mail