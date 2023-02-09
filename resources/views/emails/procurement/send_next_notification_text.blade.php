Dear {{ $nextUser->name }}

You have a procurement requisition {{ $submitType == 'submit' ? 'to attend to' : 'to be returned' }} from {{ $procurement->createdUser->name }} ({{ $procurement->unit->name }}), click link below

Procurement Requisition ID: {{ idFormatter('procurement', $procurement->id) }}
Title: {{ $procurement->title }}

{{ route('procurement/show', $procurement->id) }}

@if (!empty($currentTrail->comment))
{{ $currentTrail->user->name }}(Previous office)'s comment
{{ $currentTrail->comment }}
@endif


Thank you for using R-Plus


Auto generated mail