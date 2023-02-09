Dear {{ $nextUser->name }}<br/>
<br/>
You have "{{ $order->purchase->procurement->title }}" LPO to verify and approve, click link below<br/>
<br/>
Order Requisition ID: {{ idFormatter('order', $order->id) }}<br/>
Title: {{ $order->purchase->procurement->title }}<br/>
Supplier: {{ $order->purchase->supplier->name }}<br/>
LPO Number: {{ $order->po_number }}<br/>
<br/>
<a href="{{ route('order/show', $order->id) }}">{{ route('order/show', $order->id) }}</a><br/>
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