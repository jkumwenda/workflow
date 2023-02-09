Dear {{ $nextUser->name }}<br/>
<br/>
You have a purchase requisition {{ $submitType == 'submit' ? 'to attend to' : 'to be returned' }} from {{ $purchase->procurement->createdUser->name }} ({{ $purchase->procurement->unit->name }}), click link below<br/>
<br/>
Purchase Requisition ID: {{ idFormatter('purchase', $purchase->id) }}<br/>
Title: {{ $purchase->procurement->title }}<br/>
Supplier: {{ $purchase->supplier->name }}<br/>
<br/>
<a href="{{ route('purchase/show', $purchase->id) }}">{{ route('purchase/show', $purchase->id) }}</a><br/>
<br/>
@if (!empty($currentTrail) && !empty($currentTrail->comment))
<strong>{{ $currentTrail->user->name }}(Previous office)'s comment</strong><br/>
{{ $currentTrail->comment }}<br/>
@endif
<br/>
<br/>
Thank you for using R-Plus<br/>
<br/>
<br/>
Auto generated mail