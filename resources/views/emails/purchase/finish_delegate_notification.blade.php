Dear {{ $requester->name }}<br/>
<br/>
You have a message from {{ $sender->name }}, click link below<br/>
<br/>
Purchase Requisition ID: {{ idFormatter('purchase', $purchase->id) }}<br/>
Title: {{ $purchase->procurement->title }}<br/>
Supplier: {{ $purchase->supplier->name }}<br/>
LPO Number: {{ !is_null($purchase->order) ? $purchase->order->po_number : '' }}<br/>
<br/>
{{ route('purchase/show', $purchase->id) }}<br/>
<br/>
@if (!empty($comment))
<strong>{{ $sender->name }}'s message</strong><br/>
{!! nl2br($comment) !!}<br/>
@endif
<br/>
<br/>
Thank you for using R-Plus<br/>
<br/>
<br/>
Auto generated mail