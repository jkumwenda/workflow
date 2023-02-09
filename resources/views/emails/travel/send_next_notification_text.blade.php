Dear {{ $nextUser->name }}

You have a travel requisition {{ $submitType == 'submit' ? 'to attend to' : 'to be returned' }} from {{ $travel->procurement->createdUser->name }} ({{ $travel->procurement->unit->name }}), click link below

Travel Requisition ID: {{ idFormatter('travel', $travel->id) }}
Title: {{ $travel->procurement->title }}
Purpose: {{ $travel->purpose }}

{{ route('travel/show', $travel->id) }}

@if (!empty($currentTrail) && !empty($currentTrail->comment))
{{ $currentTrail->user->name }}(Previous office)'s comment
{{ $currentTrail->comment }}
@endif


Thank you for using R-Plus


Auto generated mail