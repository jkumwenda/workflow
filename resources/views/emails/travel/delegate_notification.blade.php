Dear {{ $receiver->name }}<br/>
<br/>
You have a delegated travel requisition to attend from {{ $travel->procurement->createdUser->name }} ({{ $travel->procurement->unit->name }}), click link below<br/>
<br/>
Travel Requisition ID: {{ idFormatter('travel', $travel->id) }}<br/>
Title: {{ $travel->procurement->title }}<br/>
Purpose: {{ $travel->purpose }}<br/>
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