Dear {{ $receiver->name }}<br/>
<br/>
You have a delegated purchase requisition to attend from {{ $purchase->procurement->createdUser->name }} ({{ $purchase->procurement->unit->name }}), click link below<br/>
<br/>
Purchase Requisition ID: {{ idFormatter('purchase', $purchase->id) }}<br/>
Title: {{ $purchase->procurement->title }}<br/>
Supplier: {{ $purchase->supplier->name }}<br/>
<br/>
{{ route('purchase/show', $purchase->id) }}<br/>
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