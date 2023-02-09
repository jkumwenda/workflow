Dear {{ $nextUser->name }}<br/>
<br/>
You have a procurement requisition {{ $submitType == 'submit' ? 'to attend to' : 'to be returned' }} from {{ $procurement->createdUser->name }} ({{ $procurement->unit->name }}), click link below<br/>
<br/>
<a href="{{ route('procurement/show', $procurement->id) }}">{{ route('procurement/show', $procurement->id) }}</a><br/>
<br/>
Procurement Requisition ID: {{ idFormatter('procurement', $procurement->id) }}<br/>
Title: {{ $procurement->title }}<br/>
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