@props(['status'])

@php
    $classes = [
        'pending'  => 'bg-warning text-dark',
        'approved' => 'bg-success',
        'rejected' => 'bg-danger',
    ];
@endphp

<span class="badge {{ $classes[$status] ?? 'bg-secondary' }}">{{ ucfirst($status) }}</span>
