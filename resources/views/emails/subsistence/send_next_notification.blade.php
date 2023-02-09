Dear {{ $nextUser->name }}<br/>
<br/>
You have a travel requisition {{ $submitType == 'submit' ? 'to attend to' : 'to be returned' }} from {{ $subsistence->travel->procurement->createdUser->name }} ({{ $subsistence->travel->procurement->unit->name }}), click link below<br/>
<br/>
<a href="{{ route('subsistence/show', $subsistence->id) }}">{{ route('subsistence/show', $subsistence->id) }}</a><br/>
<br/>
Travel Requisition ID: {{ idFormatter('travel', $subsistence->travel->procurement->id) }}<br/>
Title: {{ $subsistence->travel->procurement->title }}<br/>
<br/>
@if (!empty($currentTrail->comment))
<strong>{{ $currentTrail->user->name }}(Previous office)'s comment</strong><br/>
{!! nl2br($currentTrail->comment) !!}
@endif

<br/>
<br/>
<b>Thank you for using R-Plus</b><br/>
<br/>
<br/>
Auto generated mail