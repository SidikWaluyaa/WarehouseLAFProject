@props([
    'status'
])

@php
    $status = strtoupper($status);
    $statusColors = [
        'DRAFT' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
        'PICKING' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        'IN_TRANSIT' => 'bg-amber-500/10 text-amber-400 border-amber-500/20 animate-pulse',
        'RECEIVED' => 'bg-green-500/10 text-green-400 border-green-500/20',
        'CLOSED' => 'bg-primary/10 text-primary border-primary/20',
        'CANCELLED' => 'bg-red-500/10 text-red-400 border-red-500/20',
    ];
    $colorClass = $statusColors[$status] ?? 'bg-neutral-500/10 text-neutral-400 border-neutral-500/20';
@endphp

<span {{ $attributes->merge(['class' => "inline-block px-4 py-2 rounded-2xl text-xs font-black border uppercase tracking-wider $colorClass"]) }}>
    {{ str_replace('_', ' ', $status) }}
</span>
