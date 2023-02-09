Dear {{ $receiver->name }}<br/>
<br/>
You have a transferred voucher requisition to attend from {{ $sender->name }}, click link below<br/>
<br/>
Voucher Requisition ID: {{ idFormatter('voucher', $voucher->id) }}<br/>
Title: {{ $voucher->purchase->procurement->title }}<br/>
Supplier: {{ $voucher->purchase->supplier->name }}<br/>
Status: {{ $voucher->requisitionStatus->name }}
<br/>
{{ route('voucher/show', $voucher->id) }}<br/>
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