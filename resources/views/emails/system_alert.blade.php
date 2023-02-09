Dear RPLUS SUPPORT MEMBER<br/>
<br/>
{{$messages}}
<br/>
<br/>
@if (!empty($content))
{{ json_encode($content) }}
@endif
<br/>
See: <a href="$url">{{ $url }}</a>
<br/>
Auto generated mail