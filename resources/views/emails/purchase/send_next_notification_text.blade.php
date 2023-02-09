Dear {{ $nextUser->name }}

You have a purchase requisition {{ $submitType == 'submit' ? 'to attend to' : 'to be returned' }} from {{ $purchase->procurement->createdUser->name }} ({{ $purchase->procurement->unit->name }}), click link below

Purchase Requisition ID: {{ idFormatter('purchase', $purchase->id) }}
Title: {{ $purchase->procurement->title }}
Supplier: {{ $purchase->supplier->name }}

{{ route('purchase/show', $purchase->id) }}

@if (!empty($currentTrail) && !empty($currentTrail->comment))
{{ $currentTrail->user->name }}(Previous office)'s comment
{{ $currentTrail->comment }}
@endif


Thank you for using R-Plus


Auto generated mail