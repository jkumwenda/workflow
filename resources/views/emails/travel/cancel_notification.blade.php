Dear {{ $receiver->name }}<br/>
<br/>
The travel requisition you currently have has been canceled, click link below<br/>
<br/>
Procurement Requisition ID: {{ idFormatter('travel', $travel->id) }}<br/>
Title: {{ $travel->procurement->title }}<br/>
<br/>
{{ route('travel/show', $travel->id) }}<br/>
<br/>
@if (!empty($comment))
<strong>{{ $sender->name }}'s comment</strong><br/>
{!! nl2br($comment) !!}<br/>
@endif
<br/>
<br/>
Thank you for using R-Plus<br/>
<br/>
<br/>
Auto generated mail