Dear {{ $requester->name }}<br/>
<br/>
You have a message from {{ $sender->name }}, click link below<br/>
<br/>
Travel Requisition ID: {{ idFormatter('travel', $travel->id) }}<br/>
Title: {{ $travel->procurement->title }}<br/>
Purpose: {{ $travel->purpose }}<br/>
LPO Number: {{ !is_null($travel->order) ? $travel->order->po_number : '' }}<br/>
<br/>
{{ route('travel/show', $travel->id) }}<br/>
<br/>
@if (!empty($comment))
<strong>{{ $sender->name }}'s message</strong><br/>
{!! nl2br($comment) !!}<br/>
@endif
<br/>
<br/>
Thank you for using R-Plus<br/>
<br/>
<br/>
Auto generated mail