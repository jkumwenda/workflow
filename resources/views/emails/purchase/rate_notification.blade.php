Dear {{ $nextUser->name }}<br/>
<br/>
Your procurement requisition, {{ $purchase->procurement->title }}, has been processed. <br/>
Please rate this supplier after receiving, click link below,<br/>
<br/>
Purchase Requisition ID: {{ idFormatter('purchase', $purchase->id) }}<br/>
Title: {{ $purchase->procurement->title }}<br/>
Supplier: {{ $purchase->supplier->name }}<br/>
<br/>
<a href="{{ route('purchase/show', $purchase->id) }}">{{ route('purchase/show', $purchase->id) }}</a><br/>
<br/>
@if (!empty($currentTrail->comment))
<strong>{{ $currentTrail->user->name }}(Previous office)'s comment</strong><br/>
{{ $currentTrail->comment }}<br/>
@endif
<br/>
<br/>
Thank you for using R-Plus<br/>
<br/>
<br/>
Auto generated mail