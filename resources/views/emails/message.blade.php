Dear {{ $receiver->name }}<br/>
<br/>
You have got the message, click link below<br/>
<br/>
Requisition ID: {{ idFormatter($messageableType, $messageableId) }}<br/>
<br/>
<a href="{{ route( Str::singular($messageableType) . '/show', $messageableId) }}">{{ route( Str::singular($messageableType) . '/show', $messageableId) }}</a><br/>
<br/>
<strong>{{ $questioner->name }}'s message</strong><br/>
{!! nl2br($question) !!}<br/>
<br/>
<br/>
@if(!empty($answer))
<strong>{{ $answerer->name }}'s reply</strong><br/>
{!! nl2br($answer) !!}<br/>
<br/>
<br/>
@endif
Thank you for using R-Plus<br/>
<br/>
<br/>
Auto generated mail