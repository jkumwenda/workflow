Dear {{ $nextUser->name }}<br/>
<br/>
You have a voucher requisition {{ $submitType == 'submit' ? 'to attend to' : 'to be returned' }} from {{ $voucher->purchase->procurement->unit->name }}, click link below<br/>
<br/>
Voucher Requisition ID: {{ idFormatter('voucher', $voucher->id) }}<br/>
Title: {{ $voucher->purchase->procurement->title }}<br/>
Supplier: {{ $voucher->purchase->supplier->name }}<br/>
<br/>
<a href="{{ route('voucher/show', $voucher->id) }}">{{ route('voucher/show', $voucher->id) }}</a><br/>
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