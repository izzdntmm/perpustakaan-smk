@props(['loan'])

@php
    $icon = match ($loan->status->value) {
        'pending' => 'clock',
        'borrowed' => 'book-open',
        'returned' => 'check-circle',
        default => 'x-circle',
    };
@endphp

@if ($loan->isOverdue())
    <span {{ $attributes->class('badge badge-error gap-1') }}>
        @svg('heroicon-s-exclamation-triangle', 'size-3.5')
        Terlambat {{ $loan->daysOverdue() }} hari
    </span>
@else
    <span {{ $attributes->class(['badge gap-1', $loan->status->badgeClass()]) }}>
        @svg('heroicon-s-'.$icon, 'size-3.5')
        {{ $loan->status->label() }}
    </span>
@endif