Dear {{ $receiver->name }}<br/>
<br/>
The transport for {{ $transport->travel->procurement->title }} has be approved, click link below<br/>
<br/>
Travel Requisition ID: {{ idFormatter('travel', $transport->travel->id) }}<br/>
Title: {{ $transport->travel->procurement->title }}<br/>
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