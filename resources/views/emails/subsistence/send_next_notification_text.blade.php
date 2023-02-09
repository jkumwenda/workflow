Dear {{ $nextUser->name }}

You have a travel requisition {{ $submitType == 'submit' ? 'to attend to' : 'to be returned' }} from {{ $subsistence->travel->procurement->createdUser->name }} ({{ $subsistence->travel->procurement->unit->name }}), click link below

Travel Requisition ID: {{ idFormatter('travel', $subsistence->travel->id) }}
Title: {{ $subsistence->travel->procurement->title }}
Purpose: {{ $subsistence->travel->purpose }}

{{ route('subsistence/show', $subsistence->id) }}

@if (!empty($currentTrail) && !empty($currentTrail->comment))
{{ $currentTrail->user->name }}(Previous office)'s comment
{{ $currentTrail->comment }}
@endif


Thank you for using R-Plus


Auto generated mail