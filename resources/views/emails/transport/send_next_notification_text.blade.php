Dear {{ $nextUser->name }}

You have a travel requisition {{ $submitType == 'submit' ? 'to attend to' : 'to be returned' }} from {{ $transport->travel->procurement->createdUser->name }} ({{ $transport->travel->procurement->unit->name }}), click link below

Travel Requisition ID: {{ idFormatter('travel', $transport->travel->id) }}
Title: {{ $transport->travel->procurement->title }}
Purpose: {{ $transport->travel->purpose }}

{{ route('transport/show', $transport->id) }}

@if (!empty($currentTrail) && !empty($currentTrail->comment))
{{ $currentTrail->user->name }}(Previous office)'s comment
{{ $currentTrail->comment }}
@endif


Thank you for using R-Plus


Auto generated mail