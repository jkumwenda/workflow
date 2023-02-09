Dear {{ $receiver->name }}<br/>
<br/>
You have a delegated travel requisition to attend from {{ $subsistence->travel->procurement->createdUser->name }} ({{ $subsistence->travel->procurement->unit->name }}), click link below<br/>
<br/>
Travel Requisition ID: {{ idFormatter('travel', $subsistence->travel->id) }}<br/>
Title: {{ $subsistence->travel->procurement->title }}<br/>
Purpose: {{ $subsistence->travel->purpose }}<br/>
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