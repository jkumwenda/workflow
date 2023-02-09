Dear {{ $transport->travel->createdUser->name }}<br/>
<br/>
@if (in_array($submitType, ['submit', 'return']))
Your travel requisition, {{ $transport->travel->procurement->title }}, has been {{ $submitType == 'submit' ? 'submitted' : 'returned' }} to {{ $nextUser->name }} by {{ $nextFlow->role->description }}. <br/>
@elseif ($submitType == 'delegate')
Your travel requisition, {{ $transport->travel->procurement->title }}, has been delegated to {{ $nextUser->name }} for further processing. <br/>
@elseif ($submitType == 'cancel')
Your travel requisition, {{ $transport->travel->procurement->title }}, has been canceled by {{ $nextUser->name }}. <br/>
@elseif ($submitType == 'approve')
Your travel requisition's transport, {{ $transport->travel->procurement->title }}, has been approved by {{ $nextUser->name }}. <br/>
@elseif ($submitType == 'changeOwner')
You have become a new owner of the travel requisition, {{ $transport->travel->procurement->title }}. For more detail, see below; <br/>
@endif
<br/>
<a href="{{ route('travel/show', $transport->travel->id) }}">{{ route('travel/show', $transport->travel->id) }}</a><br/>
<br/>
<hr>
Travel Requisition ID: {{ idFormatter('travel', $transport->travel->id) }}<br/>
Title: {{ $transport->travel->procurement->title }}<br/>
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