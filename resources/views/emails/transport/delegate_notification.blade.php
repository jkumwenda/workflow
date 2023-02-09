Dear {{ $receiver->name }}<br/>
<br/>
You have a delegated travel requisition to attend from {{ $transport->travel->procurement->createdUser->name }} ({{ $transport->travel->procurement->unit->name }}), click link below<br/>
<br/>
Travel Requisition ID: {{ idFormatter('travel', $transport->travel->id) }}<br/>
Title: {{ $transport->travel->procurement->title }}<br/>
Purpose: {{ $transport->travel->purpose }}<br/>
<br/>
{{ route('transport/show', $transport->id) }}<br/>
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