Dear {{ $receiver->name }}<br/>
<br/>
You have a delegated procurement requisition to attend from {{ $procurement->createdUser->name }} ({{ $procurement->unit->name }}), click link below<br/>
<br/>
Procurement Requisition ID: {{ idFormatter('procurement', $procurement->id) }}<br/>
Title: {{ $procurement->title }}<br/>
<br/>
{{ route('procurement/show', $procurement->id) }}<br/>
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