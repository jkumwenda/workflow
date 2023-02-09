Dear {{ $receiver->name }}<br/>
<br/>
The travel requisition you currently have has been canceled, click link below<br/>
<br/>
Procurement Requisition ID: {{ idFormatter('travel', $subsistence->travel->id) }}<br/>
Title: {{ $subsistence->travel->procurement->title }}<br/>
<br/>
{{ route('subsistence/show', $subsistence->id) }}<br/>
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